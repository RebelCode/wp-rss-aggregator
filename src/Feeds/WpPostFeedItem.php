<?php

namespace RebelCode\Wpra\Core\Feeds;

use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\WpCptDataSet;
use WP_Post;

/**
 * An implementation of a data set for WP RSS Aggregator imported feed items.
 *
 * @since [*next-version*]
 */
class WpPostFeedItem extends WpCptDataSet
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

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createInnerDataSet(WP_Post $post, $metaPrefix = '', $aliases = [])
    {
        // Create the parent data set
        $parent = parent::createInnerDataSet($post, $metaPrefix, $aliases);
        // Create the feed source data set model
        $feedId = $parent['source_id'];
        $source = new WpPostFeedSource($feedId);
        // Merge the parent with an array data set that contains the "source"
        $inner = new MergedDataSet(
            new ArrayDataSet(['source' => $source,]),
            parent::createInnerDataSet($post, $metaPrefix, $aliases)
        );
        return $inner;
    }
}
