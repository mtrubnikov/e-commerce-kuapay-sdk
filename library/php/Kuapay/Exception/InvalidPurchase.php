<?php
require_once 'Kuapay/Exception.php';

class Kuapay_Exception_InvalidPurchase extends Kuapay_Exception {

    public function __construct($message = '', $code = 0, Exception $previous = null) {
        if (empty($message)) {
            $message = 'Invalid purchase';
        }

        parent::__construct($message, $code, $previous);
    }
}