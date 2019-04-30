<?php

namespace RebelCode\Wpra\Core\Feeds\Models;

use LogicException;
use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\PrefixingDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpCptDataSet;
use WP_Post;

/**
 * An implementation of a data set for WP RSS Aggregator imported feed items.
 *
 * @since 4.13
 */
class WpPostFeedItem extends WpCptDataSet
{
    /**
     * The meta key prefix.
     *
     * @since 4.13
     */
    const META_PREFIX = 'wprss_';

    /**
     * The key to which to map the feed source dataset.
     *
     * @since 4.13
     */
    const SOURCE_KEY = 'source';

    /**
     * The key to which to map the feed item author.
     *
     * @since 4.13
     */
    const AUTHOR_KEY = 'author';

    /**
     * The key to which to map the feed item URL.
     *
     * @since 4.13
     */
    const URL_KEY = 'url';

    /**
     * The key to which to map the feed item timestamp.
     *
     * @since 4.13
     */
    const TIMESTAMP_KEY = 'timestamp';

    /**
     * Constructor.
     *
     * @since 4.13
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
     * @since 4.13
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
     * @since 4.13
     */
    protected function createMetaDataSet($postOrId)
    {
        // Alias the original meta data
        $aliased = new AliasingDataSet(parent::createMetaDataSet($postOrId), [
            'permalink' => 'item_permalink',
            'source_id' => 'feed_id',
            'author' => 'item_author',
            'enclosure' => 'item_enclosure',
        ]);

        $post = $this->normalizeWpPost($postOrId);
        $wrapped = $this->wrapPostMetaDataSet($post, $aliased);

        return $wrapped;
    }

    /**
     * Wraps the post meta dataset with an additional layer that contains virtual data.
     *
     * @since 4.13
     *
     * @param WP_Post          $post The post instance for the feed item.
     * @param DataSetInterface $meta The post meta dataset.
     *
     * @return DataSetInterface The wrapped post meta dataset.
     */
    protected function wrapPostMetaDataSet(WP_Post $post, DataSetInterface $meta)
    {
        $wrapperData = [
            static::SOURCE_KEY    => new WpPostFeedSource($meta['source_id']),
            static::TIMESTAMP_KEY => strtotime($post->post_date_gmt),
        ];

        // Use the real WordPress post author if the meta author does not exist
        if (!isset($meta[static::AUTHOR_KEY]) || empty($meta[static::AUTHOR_KEY])) {
            $wrapperData[static::AUTHOR_KEY] = get_the_author_meta('display_name', $post->post_author);
        }

        // Override the URL from meta if the post type is not a WP RSS Aggregator feed item
        if ($post->post_type !== 'wprss_feed_item') {
            $wrapperData[static::URL_KEY] = get_permalink($post);
        }

        // Copy the wrapper data and replace all values with true
        // This will be used as the overrides option for the merged data set
        $overrides = array_map(function () {
            return true;
        }, $wrapperData);

        // Merge the real meta data with the wrapper dataset,
        // and explicitly set the secondary dataset to override to wrapper's entries
        $wrapper = new ArrayDataSet($wrapperData);
        $merged = new MergedDataSet($meta, $wrapper, $overrides);

        return $merged;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
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
