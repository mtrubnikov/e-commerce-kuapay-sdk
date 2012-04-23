<?php
require_once 'Kuapay/Logger.php';

class Kuapay_Logger_OpenCart extends Kuapay_Logger {
    private $logger = null;

    protected function setLogger(Log $logger) {
        $this->logger = $logger;
    }

    private function getLogger() {
        if (null === $this->logger) {
            require_once 'Kuapay/Exception/Runtime.php';
            throw new Kuapay_Exception_Runtime(sprintf('Property %s::logger is not set. Check how you call constructor.', get_class()));
        }

        return $this->logger;
    }

    public function log($message, $priority = Kuapay_Logger::INFO) {
        $message = '[Kuapay][' . strtoupper($this->priorityAsString($priority)) . ']' . $message;
        $this->getLogger()->write($message);
    }
}