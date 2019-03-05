<?php

namespace RebelCode\Wpra\Core\Feeds;

use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\PostMetaDataSet;
use WP_Post;

/**
 * An implementation of a data set for WP RSS Aggregator imported feed items.
 *
 * @since [*next-version*]
 */
class FeedItem extends ArrayDataSet
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
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'date' => $post->post_date,
            'source' => new FeedSource($post->wprss_feed_id),
        ];
        $aliases = [];
        $parent = new PostMetaDataSet($post->ID, static::META_PREFIX, [
            'url' => 'item_permalink',
            'enclosure' => 'item_enclosure',
            'author' => 'item_author',
            'source_id' => 'feed_id',
        ]);

        parent::__construct($data, $aliases, $parent);
    }
}
