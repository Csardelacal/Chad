<?php

namespace PayPal\Core;

use PayPal\Log\PayPalLogFactory;
use Psr\Log\LoggerInterface;

/**
 * Simple Logging Manager.
 * This does an error_log for now
 * Potential frameworks to use are PEAR logger, log4php from Apache
 */
class PayPalLoggingManager
{
    /**
     * @var array of logging manager instances with class name as key
     */
    private static $instances = array();

    /**
     * The logger to be used for all messages
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Logger Name
     *
     * @var string
     */
    private $loggerName;

    /**
     * Returns the singleton object
     *
     * @param string $loggerName
     * @return $this
     */
    public static function getInstance($loggerName = __CLASS__)
    {
        if (array_key_exists($loggerName, PayPalLoggingManager::$instances)) {
            return PayPalLoggingManager::$instances[$loggerName];
        }
        $instance = new self($loggerName);
        PayPalLoggingManager::$instances[$loggerName] = $instance;
        return $instance;
    }

    /**
     * Default Constructor
     *
     * @param string $loggerName Generally represents the class name.
     */
    private function __construct($loggerName)
    {
        
    }

    /**
     * Log Error
     *
     * @param string $message
     */
    public function error($message)
    {
    }

    /**
     * Log Warning
     *
     * @param string $message
     */
    public function warning($message)
    {
    }

    /**
     * Log Info
     *
     * @param string $message
     */
    public function info($message)
    {
    }

    /**
     * Log Fine
     *
     * @param string $message
     */
    public function fine($message)
    {
    }

    /**
     * Log Debug
     *
     * @param string $message
     */
    public function debug($message)
    {
    }
}
