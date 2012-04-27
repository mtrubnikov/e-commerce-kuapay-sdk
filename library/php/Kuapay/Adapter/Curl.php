<?php
require_once 'Kuapay/Adapter.php';

class Kuapay_Adapter_Curl implements Kuapay_Adapter {
    const API_URL           = 'https://www.kuapay.com/api/';
    const PURCHASE_RESOURCE = 'purchase/';
    const NEW_ACTION        = 'new';

    const GET     = 'GET';
    const POST    = 'POST';
    const TIMEOUT = 25;

    private static $defaultHeaders = array(
        'Connection: Keep-Alive',
        'Content-type: application/x-www-form-urlencoded;charset=UTF-8'
    );

    private $handle;
    private $debug;
    private $logger;
    private $userAgent;

    private $apiUrl;

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

    public function setHandle($handle) {
        $this->handle = $handle;
    }

    public function getHandle() {
        if (null === $this->handle) {
            $this->handle = curl_init();
        }

        return $this->handle;
    }

    public function setDebug($debug) {
        $this->debug = (boolean) $debug;
    }

    public function isDebug() {
        return (boolean) $this->debug;
    }

    public function setLogger(Kuapay_Logger $logger) {
        $this->logger = $logger;
    }

    public function getLogger() {
        if (null == $this->logger) {
            require_once 'Kuapay/Logger/Dummy.php';
            $this->logger = new Kuapay_Logger_Dummy();
        }

        return $this->logger;
    }

    public function setUserAgent($name) {
        $this->userAgent = $name;
    }

    public function getUserAgent() {
        if (null == $this->userAgent) {
            $this->userAgent = 'Kuapay CURL client ' . Kuapay_Version::SDK_VERSION;
        }

        return $this->userAgent;
    }

    public function setApiUrl($url) {
        $this->apiUrl = rtrim($url, '/') . '/';
    }

    public function getApiUrl() {
        if (null == $this->apiUrl) {
            $this->setApiUrl(self::API_URL . Kuapay_Version::API_VERSION . '/');
        }

        return $this->apiUrl;
    }

    public function reset() {
        $this->setHandle(null);
    }

    protected function request($url, $type = Kuapay_Adapter_Curl::GET, array $data = array()) {
        if ($this->isDebug()) {
            $this->getLogger()->log(sprintf(
                'CURL making % request to %s', $type, $url
            ));
        }

        curl_setopt($this->getHandle(), CURLOPT_URL, $url);
        curl_setopt($this->getHandle(), CURLOPT_HTTPHEADER, self::$defaultHeaders);

        curl_setopt($this->getHandle(), CURLOPT_USERAGENT, $this->getUserAgent());
        curl_setopt($this->getHandle(), CURLOPT_ENCODING , true);
        curl_setopt($this->getHandle(), CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($this->getHandle(), CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->getHandle(), CURLOPT_FOLLOWLOCATION, 1);

        if (self::POST == $type) {
            curl_setopt($this->getHandle(), CURLOPT_POST, 1);
            curl_setopt($this->getHandle(), CURLOPT_HEADER, 1);
        } else {
            curl_setopt($this->getHandle(), CURLOPT_HEADER, 0);
        }

        if (!empty($data)) {
            curl_setopt($this->getHandle(), CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $result = curl_exec($this->getHandle());

        if (!$result) {
            $errorMessage = sprintf(
                'CURL error "%s" (%s) - failed making CURL %s request to %s',
                curl_error($this->getHandle()), curl_errno($this->getHandle()), $type, $url
            );

            if ($this->isDebug()) {
                $this->getLogger()->log($errorMessage);
            }

            require_once 'Kuapay/Exception/InvalidAPIResponse.php';
            throw new Kuapay_Exception_InvalidAPIResponse($errorMessage);
        }

        curl_close($this->getHandle());
        $this->reset();

        return $result;
    }

    private function get($url, $data = array()) {
        return $this->request($url, self::GET, $data);
    }

    private function post($url, $data = array()) {
        return $this->request($url, self::POST, $data);
    }

    public function purchase(Kuapay_Purchase $purchase) {
        if (!$purchase->isValid()) {
            require_once 'Kuapay/Exception/InvalidPurchase.php';
            throw new Kuapay_Exception_InvalidPurchase();
        }

        $result = $this->post($this->getApiUrl() . self::PURCHASE_RESOURCE . self::NEW_ACTION, $purchase->toArray());

        if ($this->isDebug()) {
            $this->getLogger()->log('Retrieved result: ' . $result);
        }

        $resultParts = preg_split("~\n\r\n~", $result);

        if (!isset($resultParts[1])) {
            require_once 'Kuapay/Exception/InvalidAPIResponse.php';
            throw new Kuapay_Exception_InvalidAPIResponse('Purchase reponse is missing purchase ID');
        }

        $resultDecoded = json_decode($resultParts[1]);

        if (!is_object($resultDecoded)) {
            require_once 'Kuapay/Exception/InvalidAPIResponse.php';
            throw new Kuapay_Exception_InvalidAPIResponse((string) $resultParts[1]);
        }

        if (!isset($resultDecoded->purchase_id) || empty($resultDecoded->purchase_id)) {
            require_once 'Kuapay/Exception/InvalidAPIResponse.php';
            throw new Kuapay_Exception_InvalidAPIResponse('Parsed purchase reponse is missing purchase ID');
        }

        return $resultDecoded->purchase_id;
    }

    public function status($purchaseId) {
        $result = $this->get($this->getApiUrl() . self::PURCHASE_RESOURCE . htmlspecialchars($purchaseId));

        if ($this->isDebug()) {
            $this->getLogger()->log('Retrieved status: ' . $result);
        }

        $resultDecoded = json_decode($result);

        if (!is_object($resultDecoded)) {
            require_once 'Kuapay/Exception/InvalidAPIResponse.php';
            throw new Kuapay_Exception_InvalidAPIResponse('Status response can not be parsed');
        }

        if (!isset($resultDecoded->value)) {
            require_once 'Kuapay/Exception/InvalidAPIResponse.php';
            throw new Kuapay_Exception_InvalidAPIResponse('Parsed purchase reponse is missing status code');
        }

        if (!isset($resultDecoded->value->status_code)) {
            $resultDecoded->value->status_code = 0;
        }

        return $resultDecoded;
    }
}