<?php

namespace RebelCode\Wpra\Core\Models;

use LogicException;
use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\PrefixingDataSet;
use RebelCode\Wpra\Core\Data\WpCptDataSet;
use RebelCode\Wpra\Core\Models\WpPostFeedSource;
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
     * The key to which to map the feed source dataset.
     *
     * @since [*next-version*]
     */
    const SOURCE_KEY = 'source';

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
            'ID',
            'post_title',
            'post_content',
            'post_excerpt',
            'post_date'
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createPostDataSet($postOrId)
    {
        $postData = parent::createPostDataSet($postOrId);
        $prefixed = new PrefixingDataSet($postData, 'post_');
        $aliased = new AliasingDataSet($prefixed, ['id' => 'ID']);

        return $aliased;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createMetaDataSet($postOrId)
    {
        // Alias the original meta data
        $aliased = new AliasingDataSet(parent::createMetaDataSet($postOrId), [
            'url' => 'item_permalink',
            'source_id' => 'feed_id',
            'author' => 'item_author',
            'enclosure' => 'item_enclosure',
        ]);

        // Create the feed source data set model
        $sourceId = $aliased['source_id'];
        $sourceData = new WpPostFeedSource($sourceId);
        // Wrap it in a data set that maps it to a key
        $sourceSet = new ArrayDataSet([static::SOURCE_KEY => $sourceData]);

        // Merge the real meta data with the dataset that contains the source, and explicitly set the secondary
        // dataset to override to source key
        $merged = new MergedDataSet($aliased, $sourceSet, [static::SOURCE_KEY => true]);

        return $merged;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        if ($key === static::SOURCE_KEY) {
            throw new LogicException(
                sprintf('Cannot modify a feed item\'s "%s" data directly. Use "source_id" instead.', static::SOURCE_KEY)
            );
        }

        return parent::set($key, $value);
    }
}
