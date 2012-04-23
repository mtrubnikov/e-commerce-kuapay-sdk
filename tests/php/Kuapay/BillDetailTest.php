<?php
class Kuapay_BillDetailTest extends PHPUnit_Framework_TestCase {
    public function testSettingOptionsViaConstructor() {
        $price    = 12.5;
        $quantity = 3;
        $id       = 'SKU1234';
        $name     = 'Lorem ipsum';
        $detail   = new Kuapay_BillDetail(array(
            'id'       => $id,
            'name'     => $name,
            'quantity' => $quantity,
            'price'    => $price
        ));

        $this->assertEquals($price, $detail->getPrice());
        $this->assertEquals($quantity, $detail->getQuantity());
        $this->assertEquals($id, $detail->getId());
        $this->assertEquals($name, $detail->getName());
    }

    public function testSettingInvalidOptionViaConstructorRisesException() {
        $this->setExpectedException('Kuapay_Exception_Runtime');
        $detail = new Kuapay_BillDetail(array('nonexistent' => 'value'));
    }

    public function testConvertingToArrayWhenBillDetailIsValid() {
        $price    = 12.5;
        $quantity = 3;
        $id       = 'SKU1234';
        $name     = 'Lorem ipsum';

        $detail = new Kuapay_BillDetail();
        $this->assertEquals($detail, $detail->setId($id));
        $this->assertEquals($detail, $detail->setName($name));
        $this->assertEquals($detail, $detail->setQuantity($quantity));
        $this->assertEquals($detail, $detail->setPrice($price));
        $array = $detail->toArray();

        $this->assertEquals($id, $array['productId']);
        $this->assertEquals($name, $array['item']);
        $this->assertEquals($quantity, $array['amount']);
        $this->assertEquals($price, $array['price']);
        $this->assertEquals(array(
            'productId', 'item', 'amount', 'price'
        ), array_keys($array));
    }

    public function testConvertingToArrayWhenBillDetailIsNotValid() {
        $detail = new Kuapay_BillDetail();

        $this->setExpectedException('Kuapay_Exception_InvalidBillDetail');
        $detail->toArray();
    }

    public function testBillDetailHavingAllDataIsValid() {
        $detail   = new Kuapay_BillDetail();
        $price    = 12.5;
        $quantity = 3;
        $id       = 'SKU1234';
        $name     = 'Lorem ipsum';

        $detail = new Kuapay_BillDetail();
        $detail->setId($id);
        $detail->setName($name);
        $detail->setQuantity($quantity);
        $detail->setPrice($price);
        $this->assertTrue($detail->isValid());
    }

    public function testEmptyBillDetailIsInvalid() {
        $detail  = new Kuapay_BillDetail();

        $this->assertFalse($detail->isValid());
    }

    /**
     * @dataProvider validationTestDataProvider
     */
    public function testInvalidBillDetailValidation($id, $name, $price, $quantity) {
        $detail = new Kuapay_BillDetail();
        $detail->setId($id)
               ->setName($name)
               ->setPrice($price)
               ->setQuantity($quantity);

        $this->assertFalse($detail->isValid());
    }

    public function validationTestDataProvider() {
        return array(
            array(null, 'name', 5, 10), // invalid id
            array(1, '', 5, 10), // invalid name
            array(1, 'name', 0, 10), // price can not be ZERO
            array(1, 'name', -1, 10), // price can not be lower than ZERO
            array(1, 'name', 'a', 10), // price must be a number
            array(1, 'name', 5, 0), // quantity can not be ZERO
            array(1, 'name', 5, -1), // quantity can not be lower than ZERO
            array(1, 'name', 5, 'a') // quantity must be a number
        );
    }
}