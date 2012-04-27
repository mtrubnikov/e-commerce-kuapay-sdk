<?php
class Kuapay_Purchase {
    private $bill;
    private $email;
    private $password;
    private $serial;
    private $qrCode;

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

    public function setBill(Kuapay_Bill $bill) {
        $this->bill = $bill;

        return $this;
    }

    private function getBill() {
        return $this->bill;
    }

    public function setEmail($email) {
        $this->email = (string) $email;

        return $this;
    }

    private function getEmail() {
        return $this->email;
    }

    public function setPassword($password) {
        $this->password = (string) $password;

        return $this;
    }

    private function getPassword() {
        return $this->password;
    }

    public function setSerial($serial) {
        $this->serial = (string) $serial;

        return $this;
    }

    private function getSerial() {
        return $this->serial;
    }

    public function setQRCode($qrCode) {
        $this->qrCode = (string) $qrCode;

        return $this;
    }

    private function getQRCode() {
        return $this->qrCode;
    }

    /**
     * Checks if purchase is valid
     *
     * Valid purchase needs to have bill, email, password, serial and qr code
     *
     * @todo possibly validate email for correct email address format
     * @todo possibly check if bill has any items
     */
    public function isValid() {
        return ($this->getBill() instanceof Kuapay_Bill)
            && strlen($this->getEmail()) > 0
            && strlen($this->getPassword()) > 0
            && strlen($this->getSerial()) > 0
            && strlen($this->getQRCode()) > 0;
    }

    public function toArray() {
        if (!$this->isValid()) {
            require_once 'Kuapay/Exception/InvalidPurchase.php';
            throw new Kuapay_Exception_InvalidPurchase();
        }

        return array(
            'bill'     => $this->getBill()->toArray(),
            'serial'   => $this->getSerial(),
            'email'    => $this->getEmail(),
            'password' => $this->getPassword(),
            'qrcode'   => $this->getQRCode()
        );
    }
}