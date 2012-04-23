<?php
require_once 'Kuapay/Exception.php';

class Kuapay_Exception_InvalidAPIResponse extends Kuapay_Exception {

    public function __construct($message = '', $code = 0, Exception $previous = null) {
        if (empty($message)) {
            $message = 'Invalid API response';
        }

        parent::__construct($message, $code, $previous);
    }
}