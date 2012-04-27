<?php
require_once dirname(__FILE__) . '/../library/php/Kuapay/loadall.php';

// read purchase ID
$purchaseId = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : null;

// create Kuapay API client
$client = new Kuapay_Client();
//$client->getAdapter()->setApiUrl('https://www.kuapay.com/api/1.0');


try {
    // knowing purchase ID check purchase status
    // website should check it on regular basis and update user about progress and/or problems
    $result = $client->status($purchaseId);

    // return to client up-to-date purchase status ID
    echo json_encode(array('status_code' => $result->value->status_code)) . "\n";
} catch (Kuapay_Exception $ke) {
    // if something when wrong return to client error message
    echo json_encode(array('error' => $ke->getMessage()));
}
