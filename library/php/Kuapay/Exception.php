<?php
/**
 * Base class for Kuapay exceptions
 *
 * @author Wojciech Szela <wojtek@wikia-inc.com>
 * @link http://pl2.php.net/manual/en/class.exception.php
 */
class Kuapay_Exception extends Exception {
    /**
    * Previous exception
    *
    * @var Exception|null
    */
    private $_previous;

    /**
     * Constructor
     *
     * @link  http://pl2.php.net/manual/en/exception.construct.php
     * @param string    $message exception message
     * @param int       $code exception code
     * @param Exception $exception previous exception
     */
    public function __construct($message = '', $code = 0, Exception $previous = null) {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            parent::__construct($message, $code);
            $this->_previous = $previous;
        } else {
            parent::__construct($message, $code, $previous);
        }
    }

    /**
     * For simulating getPrevious() method introduced in PHP 5.3
     *
     * @link   http://pl2.php.net/manual/en/exception.getprevious.php
     * @param  string $method name of non-existent method called
     * @param  array  $args arguments of method call
     * @return mixed
     */
    public function __call($method, array $args) {
        if ('getprevious' == strtolower($method)) {
            return $this->_getPrevious();
        }

        require_once 'Kuapay/Exception/Runtime.php';
        throw new Kuapay_Exception_Runtime(sprintf('Method %s::%s does not exist', get_class(), $method));
    }

    /**
     * String representation of the exception
     *
     * @link http://pl2.php.net/manual/en/exception.tostring.php
     * @return string
     */
    public function __toString() {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            if (null !== ($e = $this->_getPrevious())) {
                return $e->__toString() . "\n\nNext " . parent::__toString();
            }
        }

        return parent::__toString();
    }

    /**
     * Previous exception getter
     *
     * Exception prior to PHP 5.3 don't have support of previous exceptions.
     * This is part of porting support for previous exceptions
     *
     * @return Exception|null
     */
    protected function _getPrevious() {
        return $this->_previous;
    }
}