<?php

namespace RebelCode\Wpra\Core\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * A PSR-3 logger decorator that conditionally delegates to an inner logger.
 *
 * @since [*next-version*]
 */
class ConditionalLogger extends AbstractLogger
{
    /**
     * @since [*next-version*]
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param LoggerInterface $logger  The inner logger instance.
     * @param bool            $enabled The enabled flag: true to log using the inner logger, false to not log.
     */
    public function __construct(LoggerInterface $logger, $enabled)
    {
        $this->logger = $logger;
        $this->enabled = $enabled;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->enabled) {
            $this->logger->log($level, $message, $context);
        }
    }
}
