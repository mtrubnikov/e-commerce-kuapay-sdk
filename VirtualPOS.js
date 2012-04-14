/**
 * VirtualPOS class
 * 
 * Enables a website to be able to use the VirtualPOS class.
 *
 * Dependencies: jquery 1.4
 */

var Bill = function(spec) {
  var that = this;

  that.details  = spec.details || [];
  that.total    = spec.total || 0.00;
  that.subtotal = spec.sutbotal || 0.00;
  that.tax      = spec.tax || 0.00;
};

function VirtualPOS() {
  var that = this;

  that.proxyUrl   = '/kuapos.php';

  // These are the commands in the proxy to start the 
  // commands to the Kuapos Virtual POS services
  that.commands   = {
    STATUS: "status",
    START:  "start_purchase"
  };

  that.statusCodes = {
    "0": "Started",
    "1": "Sending Bill",
    "2": "Authorizing",
    "3": "Seding Confirmation",
    "4": "Completed",
    "-1": "Error with the Identificator Code",
    "-2": "Error with the Login credentias",
    "-3": "The User has Cancelled"
  };

  that.purchaseOnProcess = null;

  that.success = function(d) {
    var result = null;
    try {
      result = JSON.parse(d);  
    } catch(e) {
      result = null;  
    }

    // Let's now check the status of the
    // proper sale
    if(result) {
      var purchase_id = result.purchase_id;
      
      that.purchaseOnProcess = window.setInterval(function() { that.intervalStatusCheck(purchase_id) }, 1000);
    }
  }
  
  that.intervalStatusCheck = function(purchase_id) {
    that.checkPurchaseStatus(
      purchase_id,
      function(r) {
        try {
          var status_p    = JSON.parse(r);
          var bill        = new Bill(status_p.value.bill);
          var status_code = status_p.value.status_code;
          that.showStatus(status_p.value.status_code);
          that.showBill(bill);
          if(parseInt(status_code) < 0 || parseInt(status_code) > 3) {
            window.clearInterval(that.purchaseOnProcess);  
          }
        }catch(e){
          console.log(r);
          console.log(e);
        }
      },
      function(r) {
        console.log('There was an error!');
        console.log(r);
      }
    ); 
  };

  that.showBill = function(billSpec) {
    // Display the bill in the screen
    // :)
  };

  that.showStatus = function(stat) {
    if(!stat) {
      stat = "0";  
    }
    if(parseInt(stat) < 0) {
      $('#status').css('color','FF5050');
    } else if(parseInt(stat) > 3) { 
      $('#status').css('color','#508050');
    } else {
      $('#status').css('color','#666');
    }
    $('#status').html(that.statusCodes[stat]);
  };

  that.error  = function(d) {
    console.log("There was an error...");
    console.log(d);  
  };

  that.checkPurchaseStatus = function(purchaseId, success, error) {
    $.ajax({
      url: that.proxyUrl,
      type: "GET",
      data: {
          action:       that.commands.STATUS,
          purchase_id:  purchaseId      
        },
      dataType: "json",
      success: success,
      error: error
    });
  };

  this.startPurchase = function(bill, qrcode) {
    console.log('calling to ' + that.proxyUrl);
    $.ajax({
      url: that.proxyUrl,
      type: "GET",
      data: {
          action:       that.commands.START,
          qrcode:       qrcode,
          bill:         bill
        },
      dataType: "json",
      success: that.success,
      error: that.error
    });
  };
};
