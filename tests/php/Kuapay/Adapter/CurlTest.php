<?php
class Kuapay_Adapter_CurlTest extends PHPUnit_Framework_TestCase {
    public function testValidOptionsPassedToConstructor() {
        $debug     = true;
        $handle    = 'handle';
        $logger    = $this->getMock('Kuapay_Logger_Dummy');
        $userAgent = get_class();

        $options = array(
            'debug'     => $debug,
            'handle'    => $handle,
            'logger'    => $logger,
            'userAgent' => $userAgent
        );

        $adapter = new Kuapay_Adapter_Curl($options);

        $this->assertEquals($debug, $adapter->isDebug());
        $this->assertEquals($handle, $adapter->getHandle());
        $this->assertEquals($logger, $adapter->getLogger());
        $this->assertEquals($userAgent, $adapter->getUserAgent());
    }

    public function testInvalidOptionPassedToConstructor() {
        $this->setExpectedException('Kuapay_Exception_Runtime');
        $adapter = new Kuapay_Adapter_Curl(array('nonexistent' => 'value'));
    }

    public function testDefaultOptions() {
        $adapter = new Kuapay_Adapter_Curl();

        $handle  = $adapter->getHandle();
        $isDebug = $adapter->isDebug();
        $logger  = $adapter->getLogger();
        $agent  = $adapter->getUserAgent();

        $this->assertInternalType('resource', $handle);
        $this->assertFalse($isDebug);
        $this->assertInstanceOf('Kuapay_Logger_Dummy', $logger);
        $this->assertContains(Kuapay_Version::SDK_VERSION, $agent);
    }

    public function testResetSetHandleToNull() {
        $handle = 'handle';
        $adapter = $this->getMock('Kuapay_Adapter_Curl', array('setHandle'));
        $adapter->setHandle($handle);
        $adapter->expects($this->once())
                ->method('setHandle')
                ->with($this->equalTo(null));
        $adapter->reset();
    }

    public function testApiUrls() {
        $this->assertEquals('https://www.kuapay.com/api/', Kuapay_Adapter_Curl::API_URL);
        $this->assertEquals('purchase/', Kuapay_Adapter_Curl::PURCHASE_RESOURCE);
        $this->assertEquals('new', Kuapay_Adapter_Curl::NEW_ACTION);
    }

    public function testMakingValidPurchase() {
        $purchaseId  = 'pid' . rand();
        $curlResult  = 'HTTP/1.1 200 OK' . "\n";
        $curlResult .= 'Content-Type: application/json' . "\n";
        $curlResult .= "\r\n";
        $curlResult .= '{"purchase_id":"' . $purchaseId . '"}';

        $purchaseData = array('data');

        $purchase = $this->getMock('Kuapay_Purchase');
        $purchase->expects($this->once())
                 ->method('toArray')
                 ->will($this->returnValue($purchaseData));
        $purchase->expects($this->once())
                 ->method('isValid')
                 ->will($this->returnValue(true));

        $curl = $this->getMock('Kuapay_Adapter_Curl', array('request'));
        $curl->expects($this->once())
             ->method('request')
             ->with(
                 $this->equalTo(Kuapay_Adapter_Curl::API_URL . Kuapay_Version::API_VERSION . '/' . Kuapay_Adapter_Curl::PURCHASE_RESOURCE . Kuapay_Adapter_Curl::NEW_ACTION),
                 $this->equalTo(Kuapay_Adapter_Curl::POST),
                 $this->equalTo($purchaseData))
             ->will($this->returnValue($curlResult));
        $curl->setDebug(true);
        $this->assertEquals($purchaseId, $curl->purchase($purchase));
    }

    public function testMakingInvalidPurchase() {
        $purchase = $this->getMock('Kuapay_Purchase');
        $purchase->expects($this->once())
                 ->method('isValid')
                 ->will($this->returnValue(false));

        $curl = $this->getMock('Kuapay_Adapter_Curl', array('request'));
        $curl->expects($this->never())
             ->method('request');

        $this->setExpectedException('Kuapay_Exception_InvalidPurchase');
        $curl->purchase($purchase);
    }

    /**
     * @dataProvider purchaseServerError
     */
    public function testMakingValidPurchaseAndEncounteringServerError($httpResponse) {
        $purchase = $this->getMock('Kuapay_Purchase');
        $purchase->expects($this->once())
                 ->method('toArray')
                 ->will($this->returnValue(array()));
        $purchase->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $curl = $this->getMock('Kuapay_Adapter_Curl', array('request'));
        $curl->expects($this->once())
             ->method('request')
             ->will($this->returnValue($httpResponse));

        $this->setExpectedException('Kuapay_Exception_InvalidAPIResponse');
        $curl->purchase($purchase);
    }

    public function purchaseServerError() {
        return array(
            array(''), // no response
            array('HTTP/1.1 404 Access Denied'), // error
            array('HTTP/1.1 200'), // missing data
            array('HTTP/1.1 200' . "\n\r\n" . '{"purchase'), // partial data
            array('HTTP/1.1 200' . "\n\r\n" . '{}'), // empty json object as response
            array('HTTP/1.1 200' . "\n\r\n" . '{"purchase_id":""}') // empty purchase id
        );
    }

    public function testMakingValidStatusCheck() {
        $purchaseId  = 'pid' . rand();
        $curlResult = '{"_id":{"$oid": "41238987dc415c5faa000002"},"uid":"123454321","value":{"bill":{"tax":0.0,"details":[{"price":5.0,"amount":1,"item":"iPod Classic"},{"price":5.0,"amount":1,"item":"Flat Shipping Rate"}],"total":10.0,"subtotal":10.0},"qrcode":"1014582755531","status":"Send Bill","status_code":1,"type":"purchase"}}';

        $curl = $this->getMock('Kuapay_Adapter_Curl', array('request'));
        $curl->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo(Kuapay_Adapter_Curl::API_URL . Kuapay_Version::API_VERSION . '/' . Kuapay_Adapter_Curl::PURCHASE_RESOURCE . $purchaseId),
                $this->equalTo(Kuapay_Adapter_Curl::GET),
                $this->equalTo(array()))
            ->will($this->returnValue($curlResult));
        $curl->setDebug(true);
        $this->assertEquals(json_decode($curlResult), $curl->status($purchaseId));
    }

    public function testMakingValidStatusCheckAndNotReceivingStatusCodeMeansCodeIsZero() {
        $purchaseId = rand();

        $curl = $this->getMock('Kuapay_Adapter_Curl', array('request'));
        $curl->expects($this->once())
             ->method('request')
             ->will($this->returnValue('{"value":{}}'));

        $result = $curl->status($purchaseId);

        $this->assertEquals(0, $result->value->status_code);
    }

    /**
     * @dataProvider statusServerError
     */
    public function testMakingValidStatusCheckAndEncounteringServerError($httpResponse, $exception) {
        $purchaseId = rand();

        $curl = $this->getMock('Kuapay_Adapter_Curl', array('request'));
        $curl->expects($this->once())
             ->method('request')
             ->will($this->returnValue($httpResponse));

        $this->setExpectedException($exception);
        $curl->status($purchaseId);
    }

    public function statusServerError() {
        return array(
            array('', 'Kuapay_Exception_InvalidAPIResponse'), // no response
            array('{"_id', 'Kuapay_Exception_InvalidAPIResponse'), // partial data
            array('{}', 'Kuapay_Exception_InvalidAPIResponse'), // empty json object as response
            // array('{"value":{}}', 'Kuapay_Exception_InvalidAPIResponse'), // empty value is ok, code zero is assumed
        );
    }
}
