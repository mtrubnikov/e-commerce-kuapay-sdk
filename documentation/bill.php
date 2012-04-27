<?php
require_once dirname(__FILE__) . '/../library/php/Kuapay/loadall.php';

// 1. Example purchase

// configuration
$serial   = '123454321';
$email    = 'your@email.com';
$password = 'password';

// QR code provided by customer
$qrCode   = isset($_REQUEST['qrcode']) ? preg_replace('~[^0-9]~', '', $_REQUEST['qrcode']) : null;

// create bill object
$bill = new Kuapay_Bill();
$bill->setSubtotal(10.50);
$bill->setTotal(12.50);
$bill->setTax(2.0);

// create items collection (list of products/services bought that will appear on the bill)
$billDetails = new Kuapay_BillDetails();

// add some items (products and services) to the collection of bought items
$billDetails->append(new Kuapay_BillDetail(array(
    "id" => 123,
    "name" => 'Awesome product',
    "quantity" => 1,
    "price" => 5
)));

$billDetails->append(new Kuapay_BillDetail(array(
    "id" => 456,
    "name" => 'New product',
    "quantity" => 1,
    "price" => 5.50
)));

// assign collection of items to the bill
$bill->setDetails($billDetails);

// create Kuapay purchase
$purchase = new Kuapay_Purchase();
$purchase->setBill($bill);
$purchase->setQRCode($qrCode);     // Capture QR from the form
$purchase->setSerial($serial);     // Set the serial of your Kuapay POS
$purchase->setEmail($email);       // Set the email of your Kuapay account
$purchase->setPassword($password); // Set the password of your Kuapay account

// create Kuapay API client
$client = new Kuapay_Client();
//$client->getAdapter()->setApiUrl('https://www.kuapay.com/api/1.0');

try {
    // try to initialize purchase
    $purchaseId = $client->purchase($purchase);

    // return to client purchase ID
    echo json_encode(array('pid' => $purchaseId)) . "\n";
} catch (Kuapay_Exception $ke) {
    // if something went wrong return to client error message
    echo json_encode(array('error' => '1' . $ke->getMessage()));
}
