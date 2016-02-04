<?php

namespace Aventura\Wprss\Core\Model;

use Aventura\Wprss\Core;

/**
 * @since 4.8.1
 */
abstract class LoggerAbstract extends Core\Plugin\ComponentAbstract implements LoggerInterface
{
    /**
     * Detailed debug information
     *
     * @since 4.8.1
     */
    const DEBUG = 100;

    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     *
     * @since 4.8.1
     */
    const INFO = 200;

    /**
     * Uncommon events
     *
     * @since 4.8.1
     */
    const NOTICE = 250;

    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     *
     * @since 4.8.1
     */
    const WARNING = 300;

    /**
     * Runtime errors
     *
     * @since 4.8.1
     */
    const ERROR = 400;

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @since 4.8.1
     */
    const CRITICAL = 500;

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     *
     * @since 4.8.1
     */
    const ALERT = 550;

    /**
     * Urgent alert.
     *
     * @since 4.8.1
     */
    const EMERGENCY = 600;

    /**
     * Logging levels from syslog protocol defined in RFC 5424
     *
     * @since 4.8.1
     * @var array $levels Logging levels
     */
    protected static $_levels = array(
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::NOTICE => 'NOTICE',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
        self::CRITICAL => 'CRITICAL',
        self::ALERT => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    );

    /**
     * @since 4.8.1
     */
    protected function _construct()
    {
        if (!$this->hasName()) {
            $this->setName('default');
        }

        parent::_construct();
    }

    /**
     * Add a log entry.
     *
     * @since 4.8.1
     * @param int $level The level of the log entry. See {@link LoggerAbstract::getLevels()}.
     * @param string $message The message of the entry. Something that can be converted to string.
     * @param array $context The context of the entry. Additional data about the environment.
     * @return LoggerAbstract This instance.
     */
    public function addRecord($level, $message, array $context = array())
    {
        $this->_addRecord($level, $message, $context);
        return $this;
    }

    /**
     * Add a log entry.
     *
     * @since 4.8.1
     * @param int $level The level of the log entry. See {@link LoggerAbstract::getLevels()}.
     * @param string $message The message of the entry. Something that can be converted to string.
     * @param array $context The context of the entry. Additional data about the environment.
     * @return LoggerAbstract This instance.
     */
    protected function _addRecord($level, $message, array $context = array())
    {
        if (!$this->shouldAddRecord($level, $message, $context)) {
            return false;
        }

        $levelName = static::getLevelName($level);
        $date      = date('Y-m-d H:i:s');
//        $format = '[%datetime%] %channel%.%level_name%: %message% %context% %extra%'; // Default format
        $format    = '[%1$s] %2$s.%3$s (%5$s): '."\n".'%4$s'."\n";
        $str       = sprintf($format, $date, // Date
            $this->getName(), // Channel
            $levelName, // Level Name
            $message, // Message
            isset($context['source']) ? $context['source'] : '' // Context
        );

        if (!($path = $this->getLogFilePath())) {
            throw $this->exception('Could not add log record: Log path must be set');
        }
        file_put_contents($path, $str, FILE_APPEND);
    }

    /**
     * Gets the name of the logging level.
     *
     * @since 4.8.1
     * @param  int    $level
     * @return string
     */
    public static function getLevelName($level)
    {
        if (!isset(static::$_levels[$level])) {
            throw new \InvalidArgumentException('Level "'.$level.'" is not defined, use one of: '.implode(', ',
                array_keys(static::$levels)));
        }
        return static::$_levels[$level];
    }

    /**
     * Get the value of the log level.
     *
     * @since 4.8.1
     * @param string $logLevel The string representation of the log level, case-insensitive.
     * @return int The numeric representation of the log level.
     */
    public static function getLogLevelValue($logLevel)
    {
        $constName = static::WPRSS_LOG_LEVEL_PREFIX.strtoupper($logLevel);
        return defined($constName) ? constant($constName) : null;
    }

    /**
     * All levels available.
     *
     * @since 4.8.1
     * @return array An array of all levels of this logger, where keys are numeric
     *  level values, and values are their string representations.
     */
    public static function getLevels()
    {
        return array_flip(static::$_levels);
    }

    /**
     * Converts PSR-3 levels to Monolog ones if necessary
     *
     * @since 4.8.1
     * @param string|int Level number (monolog) or name (PSR-3)
     * @return int
     */
    public static function toMonologLevel($level)
    {
        if (is_string($level)) {

            if (defined(get_called_class().'::'.strtoupper($level))) {
                return constant(get_called_class().'::'.strtoupper($level));
            }
            throw new \InvalidArgumentException('Level "'.$level.'" is not defined, use one of: '.implode(', ',
                array_keys(static::$levels)));
        }
        return $level;
    }

    /**
     * Adds a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @since 4.8.1
     * @param  mixed   $level   The log level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function log($level, $message, array $context = array())
    {
        $level = static::toMonologLevel($level);
        return $this->addRecord($level, (string) $message, $context);
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @since 4.8.1
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function debug($message, array $context = array())
    {
        return $this->addRecord(static::DEBUG, (string) $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @since 4.8.1
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function info($message, array $context = array())
    {
        return $this->addRecord(static::INFO, (string) $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @since 4.8.1
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function notice($message, array $context = array())
    {
        return $this->addRecord(static::NOTICE, (string) $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @since 4.8.1
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function warning($message, array $context = array())
    {
        return $this->addRecord(static::WARNING, (string) $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @since 4.8.1
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function error($message, array $context = array())
    {
        return $this->addRecord(static::ERROR, (string) $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @since 4.8.1
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function critical($message, array $context = array())
    {
        return $this->addRecord(static::CRITICAL, (string) $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @since 4.8.1
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function alert($message, array $context = array())
    {
        return $this->addRecord(static::ALERT, (string) $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @since 4.8.1
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool Whether the record has been processed
     */
    public function emergency($message, array $context = array())
    {
        return $this->addRecord(static::EMERGENCY, (string) $message, $context);
    }

    /**
     * Whether or not to add the record described by specified arguments.
     *
     * @since 4.8.1
     * @param  mixed   $level   The log level
     * @param  string  $message The log message
     * @param  array   $context The log context
     */
    public function shouldAddRecord($level, $message, array $context = array())
    {
        return true;
    }
}