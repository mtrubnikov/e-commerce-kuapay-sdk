function KuapayPOS (options) {
	var that = this;
	
	that.container    = typeof options.container != 'undefined' ? options.container : '#kuapayContainer';
	that.submitButton = typeof options.submit_button != 'undefined' ? options.submit_button : '#kuapayContainer .submit';

	that.statusCode = null;
  	that.state      = null;
  	that.states     = [ "init", "initialized", "bill", "status", "error", "success"];
  
	that.purchaseId = null;
	
	that.statusIntervalId = null;
	
	that.isRequesting = false;

	that.urlBill    = typeof options.url_bill != 'undefined' ? options.url_bill : '/kuapay-bill';
	that.urlStatus  = typeof options.url_status != 'undefined' ? options.url_status : '/kuapay-status';
	that.urlSuccess = typeof options.url_success != 'undefined' ? options.url_success : '/kuapay-success';

	that.progressBarWidth   = null;
	that.defaultQRCodeValue = typeof options.text_default_qrcode_value != 'undefined'
							? options.text_default_qrcode_value : 'Enter your Kuapay barcode number';
	
	that.textErrorConnection = typeof options.text_error_connection != 'undefined'
		? options.text_error_connection : 'Connection error';

	that.statusCodes = {
		 "0": typeof options.text_started_status != 'undefined' ? options.text_started_status : "Started",
		 "1": typeof options.text_sending_bill_status != 'undefined' ? options.text_sending_bill_status : "Sending Bill",
		 "2": typeof options.text_authorizing_status != 'undefined' ? options.text_authorizing_status : "Authorizing",
		 "3": typeof options.text_sending_confirmation_status != 'undefined' ? options.text_sending_confirmation_status : "Sending Confirmation",
		 "4": typeof options.text_completed_status != 'undefined' ? options.text_completed_status : "Completed",
		"-1": typeof options.text_error_with_identificator_code_status != 'undefined' ? options.text_error_with_identificator_code_status : "Error with the Identificator Code",
		"-2": typeof options.text_error_with_login_credentials_status != 'undefined' ? options.text_error_with_login_credentials_status : "Error with the Login credentias",
		"-3": typeof options.text_error_with_authorization_status != 'undefined' ? options.text_error_with_authorization_status : "Error with the authorization"
	};	
	
	that.drawProgressBar = function() {
		var progressBarText  = '0%';
		var progressBarWidth = '0px';
		
		switch (that.state) {
			case "init":
			case "initialized":
			case "error":
				progressBarText = '0%';
				progressBarWidth = '0px';
				break;
			case "bill":
				progressBarText = '20%';
				progressBarWidth = Math.round(that.progressBarWidth * 0.2) + 'px';
				break;
			case "status":
				switch (that.statusCode) {
					case 0:
						progressBarText = '20%';
						progressBarWidth = Math.round(that.progressBarWidth * 0.2) + 'px';
						break;
					case 1:
						progressBarText = '40%';
						progressBarWidth = Math.round(that.progressBarWidth * 0.4) + 'px';
						break;
					case 2:
						progressBarText = '60%';
						progressBarWidth = Math.round(that.progressBarWidth * 0.6) + 'px';
						break;
					case 3:
						progressBarText = '80%';
						progressBarWidth = Math.round(that.progressBarWidth * 0.8) + 'px';
						break;
					case 4:
						progressBarText = '100%';
						progressBarWidth = that.progressBarWidth + 'px';
						break;
					default:
						progressBarText = '0%';
						progressBarWidth = '0px';
						break;
				}
				break;
			case "success":
				progressBarWidth = that.progressBarWidth + 'px';
				progressBarText = '100%';
				break;
		}
		
		$('#kuapayContainer .kuapayprogressbar').animate({width: progressBarWidth}, 250);
		$('#kuapayContainer .kuapayprogressvalue').html(progressBarText);
	};
	
	that.statusCheck = function(purchaseId) {
		if ("status" == that.state && !that.isRequesting) {
			that.isRequesting = true;
			$.ajax({
				type: 'POST',
				url: that.urlStatus,
				data: { pid: purchaseId },
				dataType: 'json',		
				beforeSend: function() {
					$(that.submitButton).attr('disabled', true);
					$('#kuapayContainer .kuapaystatus').hide('fast');
				},
				error: function (xhr, ajaxOptions, thrownError){
					$('#kuapayContainer .kuapaystatus').html(that.textErrorConnection);
					$('#kuapayContainer .kuapaystatus').show('fast');
					that.isRequesting = false;
					that.state = "error";
					that.drawProgressBar();
				},
				success: function(json) {
					if (json['error']) {
						$('#kuapayContainer .kuapaystatus').html(json['error']);
						$('#kuapayContainer .kuapaystatus').show('fast');
						
						$(that.submitButton).attr('disabled', false);
						
						that.state = "error";
					}
					
					if (json['status_code']) {
						var statusCode = parseInt(json['status_code']);
						that.statusCode = statusCode;
						
						$('#kuapayContainer .kuapaystatus').hide('fast');
						
						if (statusCode == 4) {
				            window.clearInterval(that.statusIntervalId);  
				            that.state = "success";
				            setTimeout(function() {
				            	location = that.urlSuccess;
				            }, 500);
						}
						
				        if(statusCode < 0) {
				            window.clearInterval(that.statusIntervalId);
							$('#kuapayContainer .kuapaystatus').html(that.statusCodes[statusCode]);
							$('#kuapayContainer .kuapaystatus').show('fast');
							that.isRequesting = false;
							that.state = "error";
				        }
					}
					that.drawProgressBar();
					
					that.isRequesting = false;
				}
			});
		}
	};
	
	return {
		init: function() {
			that.state = 'init';
			$(that.container).html('\
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
					</div>');
			that.drawProgressBar();
			$('#kuapayContainer .kuapaystatus').hide();
			that.progressBarWidth = $('#kuapay .kuapayprogress').width();
			
			$('#kuapayContainer .qrcode').val(that.defaultQRCodeValue);
			$('#kuapayContainer .qrcode').bind('focus', function() {
				if ($('#kuapayContainer .qrcode').val() == that.defaultQRCodeValue) {
					$('#kuapayContainer .qrcode').val('');
				}
			});
			
			$('#kuapayContainer .qrcode').bind('blur', function() {
				if ($('#kuapayContainer .qrcode').val() == '') {
					$('#kuapayContainer .qrcode').val(that.defaultQRCodeValue);
				}
			});
			
			$(that.submitButton).bind('click', function() {
				that.progressBarWidth = $('#kuapayContainer .kuapayprogress').width();
				
				if (that.state != "initialized" && that.state != "error") {
					return false;
				}
				
				$.ajax({
					type: 'POST',
					url: that.urlBill,
					data: $('#kuapayContainer .kuapaypayment :input'),
					dataType: 'json',		
					beforeSend: function() {
						$(that.submitButton).attr('disabled', true);
						$('#kuapayContainer .kuapaystatus').hide('fast');
						that.statusCode = null;
						that.state = "bill";
						that.drawProgressBar();
					},
					error: function (xhr, ajaxOptions, thrownError){
						$('#kuapayContainer .kuapaystatus').html(that.textErrorConnection);
						$('#kuapayContainer .kuapaystatus').show('fast');
						that.state = "error";
						that.drawProgressBar();
					},
					success: function(json) {
						if (json['error']) {
							$('#kuapayContainer .kuapaystatus').html(json['error']);
							$('#kuapayContainer .kuapaystatus').show('fast');
							
							$(that.submitButton).attr('disabled', false);
							
							that.state = "error";
						}
						
						if (json['pid']) {
							$('#kuapayContainer .kuapaystatus').hide('fast');
							that.purchaseId = json['pid'];
							that.statusIntervalId = window.setInterval(function() {
								that.statusCheck(that.purchaseId);
							}, 1000);
							
							that.state = "status";
							that.statusCode = 0;
						}
						that.drawProgressBar();
					}
				});
			});
			
			that.state = "initialized";
		} 
	};
}
