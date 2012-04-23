<?php
class Kuapay_Logger_DummyTest extends PHPUnit_Framework_TestCase {
    public function testDummyLoggerDoesNothing() {
        $logger = new Kuapay_Logger_Dummy();
        $this->expectOutputString('');
        $logger->log('msg');
    }
}