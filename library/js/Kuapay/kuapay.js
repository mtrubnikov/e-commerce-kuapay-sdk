function KuapayPOS(options) {
    "use strict";
    var that = this;
    
    if (typeof options.container !== 'string'
        || typeof options.submit_button !== 'string'
        || typeof options.url_bill !== 'string'
        || typeof options.url_status !== 'string'
        || typeof options.url_success !== 'string'
        || typeof options.url_locale !== 'string') {
        throw { name: "Kuapay Exception", message: "KuapayPOS is not configured properly" };
    }
    
    that.statusIntervalId = null;
    
    that.isRequesting = false;
    
    that.container    = options.container;
    that.submitButton = options.submit_button;

    that.urlBill      = options.url_bill;
    that.urlStatus    = options.url_status;
    that.urlSuccess   = options.url_success;
    that.urlLocale    = options.url_locale;
    
    that.locale       = {};
    that.localeCode   = typeof options.locale === 'string' ? options.locale : 'en_US';

    that.statusCode   = null;
    that.state        = null;
    that.states       = [ "init", "initialized", "bill", "status", "error", "success"];
    
    that.uiHtml = '\
        <div class="image"></div> \
        <div class="main"> \
            <div class="kuapaypayment"> \
                <div class="qrbox"> \
                    <label for="qrcode"> \
                        <input type="text" class="text qrcode" name="qrcode"/> \
                    </label> \
                </div> \
                <div class="kuapayprogress"><div class="kuapayprogressbar"></div><div class="kuapayprogressvalue"></div></div> \
            </div> \
            <div class="kuapaystatus"></div> \
        </div>';
    
    that.loadLocale = function (locale) {
        var url = that.urlLocale;
        url = url.replace(/\/+$/, '');
        
        $.ajax({
            type: 'GET',
            url: url + '/' + that.localeCode + '.json',
            dataType: 'json',
            async: false,
            success: function(json) {
                that.locale = json;
            },
            error: function (xhr, ajaxOptions, thrownError){
                $.ajax({
                    type: 'GET',
                    url: url + '/en_US.json',
                    dataType: 'json',
                    async: false,
                    success: function(json) {
                        that.locale = json;
                    }
                });
            }
        });
    }
    
    that.drawProgressBar = function () {
        var percentWidth = 0;
        
        switch (that.state) {
            case "init":
            case "initialized":
            case "error":
                break;
            case "bill":
                percentWidth = 20;
                break;
            case "status":
                if (that.statusCode >= 0 && that.statusCode <= 4) {
                    percentWidth = (that.statusCode + 1) * 20;
                }
                break;
            case "success":
                percentWidth = 100;
                break;
        }
        
        var progressBarWidth = $('#kuapayContainer .kuapayprogress').width();
        $('#kuapayContainer .kuapayprogressbar').animate({width: Math.round(progressBarWidth * (percentWidth / 100)) + 'px'}, 250);
        $('#kuapayContainer .kuapayprogressvalue').html(percentWidth + '%');
    };
    
    that.enableSubmitButton = function() {
        $(that.submitButton).attr('disabled', false);
        $(that.submitButton).removeClass('disabled');
    };
    
    that.disableSubmitButton = function() {
        $(that.submitButton).attr('disabled', true);
        if (!$(that.submitButton).hasClass('disabled')) {
            $(that.submitButton).addClass('disabled');
        }
    };
    
    that.hideError = function() {
        $('#kuapayContainer .kuapaystatus').hide('fast');
    };
    
    that.displayError = function(message) {
        that.state = "error";
        $('#kuapayContainer .kuapaystatus').html(message);
        $('#kuapayContainer .kuapaystatus').show('fast');
        that.drawProgressBar();
    };
    
    that.sendBill = function() {
        if (that.state != "initialized" && that.state != "error") {
            return false;
        }
        
        var qrCode = $('#kuapayContainer .kuapaypayment .qrcode')[0].value;
        
        if (qrCode.length !== 13 || qrCode === that.locale.default_qr_code) {
            that.displayError(that.locale.invalid_qr_code);
        } else if (!that.isRequesting && ("initialized" === that.state || "error" === that.state)) {
            that.isRequesting = true;
            that.statusCode   = null;
            that.state        = "bill";
            
            that.disableSubmitButton();
            that.hideError();
            that.drawProgressBar();
            
            $.ajax({
                type: 'POST',
                url: that.urlBill,
                data: $('#kuapayContainer .kuapaypayment :input'),
                dataType: 'json',        
                error: function (xhr, ajaxOptions, thrownError){
                    that.displayError(that.locale.connection_error);
                    that.enableSubmitButton();
                    that.isRequesting = false;
                },
                success: function(json) {
                    if (json['error']) {
                        that.displayError(json['error']);
                    }
                    
                    if (json['pid']) {
                        that.hideError();
                        that.statusIntervalId = window.setInterval(function() {
                            that.checkStatus(json['pid']);
                        }, 1000);
                        
                        that.state = "status";
                        that.statusCode = 0;
                    }

                    that.drawProgressBar();
                    that.isRequesting = false;
                }
            });
        }
    };
    
    that.checkStatus = function(purchaseId) {
        if ("status" === that.state && !that.isRequesting) {
            that.isRequesting = true;
            that.disableSubmitButton();
            that.hideError();
            $.ajax({
                type: 'POST',
                url: that.urlStatus,
                data: { pid: purchaseId },
                dataType: 'json',        
                error: function (xhr, ajaxOptions, thrownError){
                    that.displayError(that.locale.connection_error);
                    that.isRequesting = false;
                },
                success: function(json) {
                    if (json['error']) {
                        that.displayError(json['error']);
                        that.enableSubmitButton();
                    }
                    
                    if (json['status_code']) {
                        var statusCode = parseInt(json['status_code']);
                        that.statusCode = statusCode;
                        
                        that.hideError();
                        
                        if (statusCode === 4) {
                            window.clearInterval(that.statusIntervalId);  
                            that.state = "success";
                            setTimeout(function() {
                                location = that.urlSuccess;
                            }, 500);
                        }
                        
                        if(statusCode < 0) {
                            window.clearInterval(that.statusIntervalId);
                            switch (statusCode) {
                            case -1:
                                that.displayError(that.locale.status_n1_msg);
                                break;
                            case -2:
                                that.displayError(that.locale.status_n2_msg);
                                break;
                            case -3:
                                that.displayError(that.locale.status_n3_msg);
                                break;
                            default:
                                that.displayError(that.locale.connection_error);
                                break;
                            }
                        }
                    }
                    that.drawProgressBar();
                    
                    that.isRequesting = false;
                }
            });
        }
    };
    
    that.drawUI = function() {
        $(that.container).html(that.uiHtml);
        that.drawProgressBar();
        $('#kuapayContainer .kuapaystatus').hide();
        $('#kuapayContainer .qrcode').val(that.locale.default_qr_code);
    }
    
    that.bindActions = function() {
        $('#kuapayContainer .qrcode').bind('focus', function() {
            if ($('#kuapayContainer .qrcode').val() === that.locale.default_qr_code) {
                $('#kuapayContainer .qrcode').val('');
            }
        });
        
        $('#kuapayContainer .qrcode').bind('blur', function() {
            if ($('#kuapayContainer .qrcode').val() === '') {
                $('#kuapayContainer .qrcode').val(that.locale.default_qr_code);
            }
        });
        
        $(that.submitButton).bind('click', that.sendBill);
    }
    
    return {
        init: function() {
            that.state = 'init';

            that.loadLocale(that.localeCode);
            
            that.drawUI();
            
            that.bindActions();
            
            that.state = "initialized";
        } 
    };
}
