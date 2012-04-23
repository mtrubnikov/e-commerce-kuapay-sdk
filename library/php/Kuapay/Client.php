<?php
class Kuapay_Client {
    private $adapter;

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

    private function setAdapter(Kuapay_Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getAdapter() {
        if (null === $this->adapter) {
            $this->adapter = new Kuapay_Adapter_Curl();
        }

        return $this->adapter;
    }

    public function purchase(Kuapay_Purchase $purchase) {
        return $this->getAdapter()->purchase($purchase);
    }

    public function status($purchaseId) {
        return $this->getAdapter()->status($purchaseId);
    }
}