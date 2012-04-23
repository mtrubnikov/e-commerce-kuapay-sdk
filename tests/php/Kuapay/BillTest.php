<?php
class Kuapay_BillTest extends PHPUnit_Framework_TestCase {
    public function testSettingOptionsViaConstructor() {
        $subtotal = 12.5;
        $tax      = 3.25;
        $total    = $subtotal + $tax;
        $details  = new Kuapay_BillDetails();
        $bill     = new Kuapay_Bill(array(
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'total'    => $total,
            'details'  => $details
        ));

        $array = $bill->toArray();

        $this->assertEquals(array(), $array['details']);
        $this->assertEquals($subtotal, $array['subtotal']);
        $this->assertEquals($tax, $array['tax']);
        $this->assertEquals($total, $array['total']);
    }

    public function testSettingInvalidOptionViaConstructorRisesException() {
        $this->setExpectedException('Kuapay_Exception_Runtime');
        $bill = new Kuapay_Bill(array('nonexistent' => 'value'));
    }

    public function testConvertingToArrayWhenBillIsValid() {
        $details  = new Kuapay_BillDetails();
        $subtotal = 12.5;
        $tax      = 7;
        $total    = 19.5;

        $bill = new Kuapay_Bill();
        $this->assertEquals($bill, $bill->setDetails($details));
        $this->assertEquals($bill, $bill->setSubtotal($subtotal));
        $this->assertEquals($bill, $bill->setTax($tax));
        $this->assertEquals($bill, $bill->setTotal($total));
        $array = $bill->toArray();

        $this->assertEquals(array(), $array['details']);
        $this->assertEquals($subtotal, $array['subtotal']);
        $this->assertEquals($tax, $array['tax']);
        $this->assertEquals($total, $array['total']);
        $this->assertEquals(array(
            'details', 'subtotal', 'tax', 'total'
        ), array_keys($array));
    }

    public function testConvertingToArrayWhenBillIsNotValid() {
        $bill = new Kuapay_Bill();

        $this->setExpectedException('Kuapay_Exception_InvalidBill');
        $bill->toArray();
    }

    public function testBillHavingAllDataAndSumsMatchingIsValid() {
        $details  = new Kuapay_BillDetails();
        $subtotal = rand();
        $tax      = rand();
        $total    = $subtotal + $tax;

        $this->assertEquals($total, $subtotal + $tax);

        $bill = new Kuapay_Bill();
        $bill->setDetails($details)
             ->setSubtotal($subtotal)
             ->setTax($tax)
             ->setTotal($total);

        $this->assertTrue($bill->isValid());
    }

    public function testEmptyBillIsInvalid() {
        $bill = new Kuapay_Bill();
        $this->assertFalse($bill->isValid());
    }

    /**
     * @dataProvider validationTestDataProvider
     */
    public function testInvalidBillValidation($details, $subtotal, $tax, $total) {
        $bill = new Kuapay_Bill();
        $bill->setSubtotal($subtotal)
             ->setTax($tax)
             ->setTotal($total);
        if ($details) {
            $bill->setDetails($details);
        }

        $this->assertFalse($bill->isValid());
    }

    public function validationTestDataProvider() {
        return array(
            array(new Kuapay_BillDetails(), -1, 1, 0), // total is ZERO
            array(new Kuapay_BillDetails(), -1, 0, -1), // total is BELOW ZERO
            array(new Kuapay_BillDetails(), 1, 1, 1), // total does not sum up
            array(null, 1, 1, 2) // missing bill details (items)
        );
    }
}