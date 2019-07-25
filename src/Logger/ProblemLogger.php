<?php

namespace RebelCode\Wpra\Core\Logger;

use Psr\Log\NullLogger;

/**
 * A special null logger that is used when an error occurs and the original logger cannot be used.
 *
 * @since [*next-version*]
 */
class ProblemLogger extends NullLogger implements LogReaderInterface, ClearableLoggerInterface
{
    /**
     * The error.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    public $error;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $error The error.
     */
    public function __construct($error)
    {
        $this->error = $error;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function clearLogs()
    {
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getLogs($num = null, $page = 1)
    {
        return [];
    }
}
