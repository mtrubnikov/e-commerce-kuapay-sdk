<?php
class Kuapay_PurchaseTest extends PHPUnit_Framework_TestCase {
    public function testSettingOptionsViaConstructor() {
        $email    = 'validemail@kuapay.com';
        $password = 'Lorem ipsum';
        $serial   = 'serial number';
        $qrCode   = 'qrcode';
        $bill     = $this->getMock('Kuapay_Bill');
        $bill->expects($this->once())
             ->method('toArray')
             ->will($this->returnValue(array()));

        $purchase = new Kuapay_Purchase(array(
            'email'    => $email,
            'password' => $password,
            'serial'   => $serial,
            'qrcode'   => $qrCode,
            'bill'     => $bill
        ));

        $array = $purchase->toArray();

        $this->assertEquals(array(), $array['bill']);
        $this->assertEquals($email, $array['email']);
        $this->assertEquals($password, $array['password']);
        $this->assertEquals($serial, $array['serial']);
        $this->assertEquals($qrCode, $array['qrcode']);
    }

    public function testSettingInvalidOptionViaConstructorRisesException() {
        $this->setExpectedException('Kuapay_Exception_Runtime');
        $purchase = new Kuapay_Purchase(array('nonexistent' => 'value'));
    }

    public function testConvertingToArrayWhenPurchaseIsValid() {
        $email    = 'validemail@kuapay.com';
        $password = 'Lorem ipsum';
        $serial   = 'serial number';
        $qrCode   = 'qrcode';
        $bill     = $this->getMock('Kuapay_Bill');
        $bill->expects($this->once())
             ->method('toArray')
             ->will($this->returnValue(array()));

        $purchase = new Kuapay_Purchase();
        $this->assertEquals($purchase, $purchase->setEmail($email));
        $this->assertEquals($purchase, $purchase->setPassword($password));
        $this->assertEquals($purchase, $purchase->setSerial($serial));
        $this->assertEquals($purchase, $purchase->setQRCode($qrCode));
        $this->assertEquals($purchase, $purchase->setBill($bill));
        $array = $purchase->toArray();

        $this->assertEquals(array(), $array['bill']);
        $this->assertEquals($email, $array['email']);
        $this->assertEquals($password, $array['password']);
        $this->assertEquals($serial, $array['serial']);
        $this->assertEquals($qrCode, $array['qrcode']);
        $this->assertEquals(array(
            'bill', 'serial', 'email', 'password', 'qrcode'
        ), array_keys($array));
    }

    public function testConvertingToArrayWhenPurchaseIsNotValid() {
        $purchase = new Kuapay_Purchase();

        $this->setExpectedException('Kuapay_Exception_InvalidPurchase');
        $purchase->toArray();
    }

    public function testEmptyPurchaseIsInvalid() {
        $purchase = new Kuapay_Purchase();
        $this->assertFalse($purchase->isValid());
    }

    /**
     * @dataProvider validationTestDataProvider
     */
    public function testInvalidPurchaseValidation($bill, $email, $password, $serial, $qrcode) {
        $purchase = new Kuapay_Purchase();
        $purchase->setEmail($email)
                 ->setPassword($password)
                 ->setSerial($serial)
                 ->setQRCode($qrcode);
        if ($bill) {
            $purchase->setBill($bill);
        }

        $this->assertFalse($purchase->isValid());
    }

    public function validationTestDataProvider() {
        return array(
            array(new Kuapay_Bill(), 'validemail@kuapay.com', 'Lorem ipsum', 'serial number', ''), // empty qr code
            array(new Kuapay_Bill(), 'validemail@kuapay.com', 'Lorem ipsum', '', 'qrcode'), // empty serial
            array(new Kuapay_Bill(), 'validemail@kuapay.com', '', 'serial number', 'qrcode'), // empty empty password
            array(new Kuapay_Bill(), '', 'Lorem ipsum', 'serial number', 'qrcode'), // empty email
            array(null, 'validemail@kuapay.com', 'Lorem ipsum', 'serial number', 'qrcode'), // empty bill
        );
    }
}