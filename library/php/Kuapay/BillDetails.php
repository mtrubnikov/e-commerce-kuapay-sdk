<?php
class Kuapay_BillDetails extends ArrayObject {
    public function offsetSet($index, $newval) {
        if (!($newval instanceof Kuapay_BillDetail)) {
            require_once 'Kuapay/Exception/InvalidDataType.php';
            throw new Kuapay_Exception_InvalidDataType();
        }

        parent::offsetSet($index, $newval);
    }

    public function append($value) {
        if (!($value instanceof Kuapay_BillDetail)) {
            require_once 'Kuapay/Exception/InvalidDataType.php';
            throw new Kuapay_Exception_InvalidDataType();
        }

        parent::append($value);
    }


    public function toArray() {
        $array = array();

        foreach ($this as $detail) {
            $array[] = $detail->toArray();
        }

        return $array;
    }
}