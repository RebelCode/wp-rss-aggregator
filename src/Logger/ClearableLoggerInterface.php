<?php

namespace RebelCode\Wpra\Core\Logger;

/**
 * An interface for loggers that can be cleared of previously logged messages.
 *
 * @since [*next-version*]
 */
interface ClearableLoggerInterface
{
    /**
     * Clears all previously logged messages.
     *
     * @since [*next-version*]
     *
     * @return void
     */
    public function clearLogs();
}
