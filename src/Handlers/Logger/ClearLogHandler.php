<?php

namespace RebelCode\Wpra\Core\Handlers\Logger;

use RebelCode\Wpra\Core\Logger\ClearableLoggerInterface;

/**
 * Handles log clearing requests.
 *
 * @since [*next-version*]
 */
class ClearLogHandler
{
    /**
     * @since [*next-version*]
     *
     * @var ClearableLoggerInterface
     */
    protected $logger;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $nonceName;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ClearableLoggerInterface $logger    The logger.
     * @param string                   $nonceName The name of the nonce to verify requests.
     */
    public function __construct(ClearableLoggerInterface $logger, $nonceName)
    {
        $this->logger = $logger;
        $this->nonceName = $nonceName;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $clearLog = filter_input(INPUT_POST, 'wpra-clear-log', FILTER_DEFAULT);

        if (empty($clearLog) || !check_admin_referer($this->nonceName)) {
            return;
        }

        $this->logger->clearLogs();
    }
}
