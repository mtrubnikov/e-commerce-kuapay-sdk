<?php
abstract class Kuapay_Logger {
    const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages

    private static $priorities = array(
        self::EMERG =>  'emerg',
        self::ALERT =>  'alert',
        self::CRIT =>   'crit',
        self::ERR =>    'err',
        self::WARN =>   'warn',
        self::NOTICE => 'notice',
        self::INFO =>   'info',
        self::DEBUG =>  'debug'
    );

    public function __construct(array $options = array()) {
        $this->setOptions($options);
    }

    private function setOptions(array $options = array()) {
        foreach ($options as $name => $value) {
            $method = 'set' . ucfirst($name);

            if (!method_exists($this, $method)) {
                require_once 'Kuapay/Exception/Runtime.php';
                throw new Kuapay_Exception_Runtime(sprintf('Method %s::%s does not exist', get_class(), $method));
            }

            $this->$method($value);
        }
    }

    protected static function priorityAsString($priority) {
        if (isset(self::$priorities[$priority])) {
            return self::$priorities[$priority];
        }

        return 'unknown';
    }

    abstract public function log($message, $priority = Kuapay_Logger::INFO);
}