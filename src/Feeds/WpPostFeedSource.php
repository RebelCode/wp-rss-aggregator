<?php

namespace RebelCode\Wpra\Core\Feeds;

use RebelCode\Wpra\Core\Data\WpCptDataSet;
use WP_Post;

/**
 * Model class for posts of the WP RSS Aggregator feed source custom post type.
 *
 * @since [*next-version*]
 */
class WpPostFeedSource extends WpCptDataSet
{
    /**
     * The meta prefix.
     *
     * @since [*next-version*]
     */
    const META_PREFIX = 'wprss_';

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $post The post instance or ID.
     */
    public function __construct($post)
    {
        parent::__construct($post, static::META_PREFIX);
    }
}
