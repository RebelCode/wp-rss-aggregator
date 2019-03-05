<?php

namespace RebelCode\Wpra\Core\Feeds;

use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\PostMetaDataSet;
use WP_Post;

/**
 * An implementation of a data set for WP RSS Aggregator feed sources.
 *
 * @since [*next-version*]
 */
class FeedSource extends ArrayDataSet
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
        $post = ($post instanceof WP_Post) ? $post : get_post($post);
        $data = [
            'id' => $post->ID,
            'title' => $post->post_title,
        ];
        $aliases = [];
        $parent = new PostMetaDataSet($post->ID, static::META_PREFIX);

        parent::__construct($data, $aliases, $parent);
    }
}
