<?php
set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR,
    get_include_path(),
)));

require_once 'Kuapay/Adapter.php';
require_once 'Kuapay/Bill.php';
require_once 'Kuapay/BillDetail.php';
require_once 'Kuapay/BillDetails.php';
require_once 'Kuapay/Client.php';
require_once 'Kuapay/Exception.php';
require_once 'Kuapay/Logger.php';
require_once 'Kuapay/Purchase.php';
require_once 'Kuapay/Version.php';
require_once 'Kuapay/Adapter/Curl.php';
require_once 'Kuapay/Exception/InvalidBill.php';
require_once 'Kuapay/Exception/InvalidBillDetail.php';
require_once 'Kuapay/Exception/InvalidCredentials.php';
require_once 'Kuapay/Exception/InvalidDataType.php';
require_once 'Kuapay/Exception/InvalidPurchase.php';
require_once 'Kuapay/Exception/InvalidQRCode.php';
require_once 'Kuapay/Exception/Runtime.php';
require_once 'Kuapay/Exception/TransactionNotAuthorized.php';
require_once 'Kuapay/Logger/Dummy.php';
require_once 'Kuapay/Logger/OpenCart.php';
