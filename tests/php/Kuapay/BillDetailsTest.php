<?php
class Kuapay_BillDetailsTest extends PHPUnit_Framework_TestCase {
    public function testOffsetSetAcceptsBillDetailObjectsOnly() {
        $details = new Kuapay_BillDetails();

        $billDetail = new Kuapay_BillDetail();
        $details->offsetSet(0, $billDetail);
        $this->assertEquals($billDetail, $details[0]);

        $this->setExpectedException('Kuapay_Exception_InvalidDataType');
        $details->offsetSet(0, new stdClass());
    }

    public function testOnlyBillDetailsCanBeSetUsingArrayInterface() {
        $details = new Kuapay_BillDetails();

        $billDetail = new Kuapay_BillDetail();
        $details[0] = $billDetail;
        $this->assertEquals($billDetail, $details[0]);

        $this->setExpectedException('Kuapay_Exception_InvalidDataType');
        $details[0] =  new stdClass();
    }

    public function testAppendAcceptsBillDetailObjectsOnly() {
        $details = new Kuapay_BillDetails();

        $billDetail = new Kuapay_BillDetail();
        $details->append($billDetail);
        $this->assertEquals($billDetail, $details[0]);

        $this->setExpectedException('Kuapay_Exception_InvalidDataType');
        $details->append(new stdClass());
    }

    public function testOnlyBillDetailsCanBeAppendedUsingArrayInterface() {
        $details = new Kuapay_BillDetails();

        $billDetail = new Kuapay_BillDetail();
        $details[] = $billDetail;
        $this->assertEquals($billDetail, $details[0]);

        $this->setExpectedException('Kuapay_Exception_InvalidDataType');
        $details[] = new stdClass();
    }

    public function testConvertingToArray() {
        $detailA = new Kuapay_BillDetail(array(
            'id'       => 1,
            'name'     => 'a',
            'quantity' => 2,
            'price'    => 3.45
        ));
        $detailB = new Kuapay_BillDetail(array(
            'id'       => 6,
            'name'     => 'b',
            'quantity' => 7,
            'price'    => 8.09
        ));
        $details = new Kuapay_BillDetails();
        $details[] = $detailA;
        $details[] = $detailB;

        $array = $details->toArray();

        $this->assertEquals(2, count($array));

        foreach ($array as $key => $value) {
            $this->assertInternalType('array', $value);
        }

        $expectedArray = array(
            0 => array(
    			'productId' => 1,
    			'item' => 'a',
    			'amount' => 2,
    			'price' => 3.45,
            ),
            1 => array (
            	'productId' => 6,
    			'item' => 'b',
    			'amount' => 7,
   				'price' => 8.09,
   			),
        );
        $this->assertEquals($expectedArray, $array);
    }
}