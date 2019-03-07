<?php

namespace RebelCode\Wpra\Core\Models;

use RebelCode\Wpra\Core\Data\WpCptDataSet;
use WP_Post;

/**
 * An implementation of a data set for WP RSS Aggregator imported feed items.
 *
 * @since [*next-version*]
 */
class FeedItem extends WpCptDataSet
{
    /**
     * The meta key prefix.
     *
     * @since [*next-version*]
     */
    const META_PREFIX = 'wprss_';

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $post The WordPress Post object or post ID.
     */
    public function __construct($post)
    {
        parent::__construct($post, static::META_PREFIX, [
            'url' => 'item_permalink',
            'source_id' => 'feed_id',
            'author' => 'item_author',
            'enclosure' => 'item_enclosure',
        ]);
    }
}
