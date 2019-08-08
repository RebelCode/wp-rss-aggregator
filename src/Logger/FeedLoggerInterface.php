<?php

namespace RebelCode\Wpra\Core\Logger;

use Psr\Log\LoggerInterface;

/**
 * Interface for a logger that can log messages for specific feed sources.
 *
 * @since [*next-version*]
 */
interface FeedLoggerInterface extends LoggerInterface
{
    /**
     * Returns a copy of the logger instance that logs messages related to a particular feed source.
     *
     * @since [*next-version*]
     *
     * @param int $feedId The ID of the feed source.
     *
     * @return LoggerInterface
     */
    public function forFeedSource($feedId);
}
