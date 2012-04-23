<?php
class Kuapay_Logger_OpenCartTest extends PHPUnit_Framework_TestCase {
    private static $openCartLog;

    protected function getOpenCartLog() {
        if (null === self::$openCartLog) {
            self::$openCartLog = $this->getMock('Log', array('write'), array());
        }

        return self::$openCartLog;
    }

    public function testLoggerWritesToOpenCartLog() {
        $msg = 'logger test';

        $this->getOpenCartLog()->expects($this->once())
             ->method('write')
             ->with($this->equalTo('[Kuapay][INFO]' . $msg));

        $logger = new Kuapay_Logger_OpenCart(array(
            'logger' => $this->getOpenCartLog()
        ));
        $logger->log($msg);
    }

    public function testWritingLogOfUnknownPriority() {
        $msg = 'logger test';

        $this->getOpenCartLog()->expects($this->once())
                          ->method('write')
                          ->with($this->equalTo('[Kuapay][UNKNOWN]' . $msg));

        $logger = new Kuapay_Logger_OpenCart(array(
            'logger' => $this->getOpenCartLog()
        ));
        $logger->log($msg, 'rand');
    }

    public function testCallingConstructorWithUnknownOptions() {
        $this->setExpectedException('Kuapay_Exception_Runtime');
        $logger = new Kuapay_Logger_OpenCart(array(
    		'nonexistent' => $this->getOpenCartLog()
        ));
    }

    public function testLoggingInformationWhenNoLoggerIsAvailable() {
        $this->setExpectedException('Kuapay_Exception_Runtime');
        $logger = new Kuapay_Logger_OpenCart();
        $logger->log('msg');
    }
}