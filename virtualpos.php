<?php
// PHP to handle the Virtual POS calls
// This is needed in order to protect the data and enable the AJAX 
// to be able to handle the requests

// Error reporting to maximum
// error_reporting(E_ERROR | E_PARSE);

// Curl needed
include 'lib/curl.php';
$cc = new cURL();

$_POST = array_merge($_POST, $_GET);

// Configuration of the Node to do the purchase to
// ================================================== EDIT HERE
$baseSERVER   = "https://www.kuapay.com";     // Set this to the node of your account
// ================================================== EDIT HERE
$baseURI      = $baseSERVER . "/api/1.0/";
$baseResource = "purchase/";
$baseAction   = "new";

// Configutation variables Insert here your information
// ================================================== EDIT HERE
$userEmail      = "xxx@yyy.com";              // Kuapay account email
$userPassword   = "password";                 // Kuapay account password
$userPosSerial  = "1234567890";               // Kuapay account serial
// ================================================== EDIT HERE

$action       = $_POST['action'];

if($action == "status") {
  $purchase_id  = $_POST['purchase_id'];
  if(sizeof($purchase_id) < 200) {
    $result = $cc->get($baseURI . $baseResource . $purchase_id);
    print json_encode($result);
  }
} else if($action == "start_purchase") {
  // Here is where you need to add the code to
  // obtaine the bill total. The structure of the bill
  // has to be the same one as the sample one
  //
  // purchase: {
  //   details: [
  //     { 
  //       item:    item name,
  //       amount:  amount of items,
  //       price:   unitary price per unit
  //     }],
  //   total:   total amount to be charged,
  //   }
  //
  // ================================================== OBTAIN BILL

  $sample_purchase = array();
  $sample_purchase["details"] = array();
  $sample_purchase["details"][0] = array();
  $sample_purchase["details"][0]["item"] = "Sample Item Name";
  $sample_purchase["details"][0]["amount"] = 1;
  $sample_purchase["details"][0]["price"] = 10.00;
  $sample_purchase["subtotal"] = 10.0;
  $sample_purchase["total"] = 11.00;
  $sample_purchase["tax"]   = 0.1;

  $purchase = array();
  $purchase["bill"] = $sample_purchase;
  // ================================================== OBTAIN BILL

  $qrcode               = $_POST['qrcode'];
  if(sizeof($qrcode) <= 32) {
    $purchase["serial"]   = $userPosSerial;
    $purchase["email"]    = $userEmail;
    $purchase["password"] = $userPassword;
    $purchase["qrcode"]   = $qrcode;
 
    $result = $cc->post($baseURI . $baseResource . $baseAction, http_build_query($purchase));

    $result_parsed = split("\n\r\n", $result);
    print json_encode($result_parsed[1]);
  }
}
?>

