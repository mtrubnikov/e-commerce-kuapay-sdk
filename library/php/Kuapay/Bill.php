<?php
class Kuapay_Bill {
    private $details;
    private $subtotal;
    private $total;
    private $tax;

    public function __construct(array $options = array()) {
        $this->setOptions($options);
    }

    private function setOptions(array $options = array()) {
        foreach ($options as $name => $value) {
            $method = 'set' . ucfirst($name);

            if (!method_exists($this, $method)) {
                require_once 'Kuapay/Exception/Runtime.php';
                throw new Kuapay_Exception_Runtime(sprintf(
                    'Method %s::%s does not exist', get_class(), $method
                ));
            }

            $this->$method($value);
        }
    }

    public function setDetails(Kuapay_BillDetails $details) {
        $this->details = $details;

        return $this;
    }

    private function getDetails() {
        return $this->details;
    }

    public function setSubtotal($subtotal) {
        $this->subtotal = $subtotal;

        return $this;
    }

    private function getSubtotal() {
        return $this->subtotal;
    }

    public function setTotal($total) {
        $this->total = $total;

        return $this;
    }

    private function getTotal() {
        return $this->total;
    }

    public function setTax($tax) {
        $this->tax = $tax;

        return $this;
    }

    private function getTax() {
        return $this->tax;
    }

    public function isValid() {
        return (($this->getDetails() instanceof Kuapay_BillDetails)
            && $this->getTotal() >= 0);
    }

    public function toArray() {
        if (!$this->isValid()) {
            require_once 'Kuapay/Exception/InvalidBill.php';
            throw new Kuapay_Exception_InvalidBill();
        }

        return array(
            'details'  => $this->getDetails()->toArray(),
            'subtotal' => $this->getSubtotal(),
            'tax'      => $this->getTax(),
            'total'    => $this->getTotal()
        );
    }
}