<?php

namespace RebelCode\Wpra\Core\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * A PSR-3 logger decorator that conditionally delegates to an inner logger.
 *
 * @since 4.14
 */
class ConditionalLogger extends AbstractLogger
{
    /**
     * @since 4.14
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @since 4.14
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Constructor.
     *
     * @since 4.14
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
     * @since 4.14
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->enabled) {
            $this->logger->log($level, $message, $context);
        }
    }
}
