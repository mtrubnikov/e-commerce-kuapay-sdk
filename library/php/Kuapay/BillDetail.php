<?php
class Kuapay_BillDetail {
    private $id;
    private $name;
    private $quantity;
    private $price;

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

    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setPrice($price) {
        $this->price = $price;

        return $this;
    }

    public function getPrice() {
        return $this->price;
    }

    public function isValid() {
        return strlen($this->getId()) > 0
            && $this->getQuantity() > 0
            && $this->getPrice() > 0
            && strlen($this->getName()) > 0;
    }

    public function toArray() {
        if (!$this->isValid()) {
            require_once 'Kuapay/Exception/InvalidBillDetail.php';
            throw new Kuapay_Exception_InvalidBillDetail();
        }

        return array(
            "productId" => $this->getId(),
            "item"      => htmlspecialchars($this->getName()),
            "amount"    => $this->getQuantity(),
            "price"     => $this->getPrice()
        );
    }
}