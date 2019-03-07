<?php

namespace RebelCode\Wpra\Core\Templates;

use RebelCode\Wpra\Core\Data\WpCptDataSet;
use WP_Post;

/**
 * A feed template model implementation for standard WP RSS Aggregator templates that are stored as a CPT.
 *
 * @since [*next-version*]
 */
class WpPostFeedTemplate extends WpCptDataSet
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
