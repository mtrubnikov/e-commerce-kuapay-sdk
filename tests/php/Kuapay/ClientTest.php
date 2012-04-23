<?php
class Kuapay_ClientTest extends PHPUnit_Framework_TestCase {
    public function testSettingOptionsViaConstructor() {
        $adapter = new Kuapay_Adapter_Curl();
        $client   = new Kuapay_Client(array(
            'adapter' => $adapter
        ));

        $this->assertEquals($adapter, $client->getAdapter());
    }

    public function testSettingInvalidOptionViaConstructorRisesException() {
        $this->setExpectedException('Kuapay_Exception_Runtime');
        $client = new Kuapay_Client(array('nonexistent' => 'value'));
    }

    public function testDefaultAdapterIsCurl() {
        $client = new Kuapay_Client();
        $this->assertInstanceOf('Kuapay_Adapter_Curl', $client->getAdapter());
    }

    public function testMakingPurchase() {
        $purchase   = new Kuapay_Purchase();
        $purchaseId = rand();
        $adapter    = $this->getMock('Kuapay_Adapter_Curl', array('purchase'));
        $adapter->expects($this->once())
                ->method('purchase')
                ->with($this->equalTo($purchase))
                ->will($this->returnValue($purchaseId));

        $client = new Kuapay_Client(array('adapter' => $adapter));

        $this->assertEquals($purchaseId, $client->purchase($purchase));
    }

    public function testCheckingTransactionStatus() {
        $statusResponse = new stdClass();
        $purchaseId     = rand();
        $adapter        = $this->getMock('Kuapay_Adapter_Curl', array('status'));
        $adapter->expects($this->once())
                ->method('status')
                ->with($this->equalTo($purchaseId))
                ->will($this->returnValue($statusResponse));

        $client = new Kuapay_Client(array('adapter' => $adapter));

        $this->assertEquals($statusResponse, $client->status($purchaseId));

    }
}