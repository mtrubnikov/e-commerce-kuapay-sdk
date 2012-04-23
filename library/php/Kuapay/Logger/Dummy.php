<?php
require_once 'Kuapay/Logger.php';

class Kuapay_Logger_Dummy extends Kuapay_Logger {
    /**
     * Does not do anything by design
     *
     * @see Kuapay_Logger::log()
     */
    public function log($message, $priority = Kuapay_Logger::INFO) {
    }
}