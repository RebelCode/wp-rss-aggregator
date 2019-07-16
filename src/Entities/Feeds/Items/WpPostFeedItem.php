<?php

namespace RebelCode\Wpra\Core\Entities\Feeds\Items;

use LogicException;
use OutOfRangeException;
use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\PrefixingDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpCptDataSet;
use RebelCode\Wpra\Core\Entities\Feeds\Sources\WpPostFeedSource;
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
     * The second item-specific meta key prefix.
     *
     * @since 4.14
     */
    const SECOND_META_PREFIX = 'wprss_item_';

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
     * The key to which to map the item featured image.
     *
     * @since 4.14
     */
    const FT_IMAGE_KEY = 'ft_image_url';

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param int|string|WP_Post $post The WordPress Post object or post ID.
     *
     * @throws OutOfRangeException If the post does not exist.
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
        // Add the second meta prefix
        $prefixed = new PrefixingDataSet(parent::createMetaDataSet($postOrId), static::SECOND_META_PREFIX);

        // Alias some of the meta data
        $aliased = new AliasingDataSet($prefixed, [
            'source_id' => 'feed_id',
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
        $source = new WpPostFeedSource($meta['source_id']);
        $importSource = filter_var($source['import_source'], FILTER_VALIDATE_BOOLEAN);

        $wrapperData = [
            static::SOURCE_KEY    => $source,
            static::TIMESTAMP_KEY => strtotime($post->post_date_gmt),
            static::FT_IMAGE_KEY => $this->getFtImageUrl($post),
            static::URL_KEY => $this->getItemUrl($post, $meta, $source),
            'source_name' => $importSource ? $meta['source_name'] : $source['title'],
            'source_url' => $importSource ? $meta['source_url'] : $source['site_url'],
        ];

        // For non-WPRSS feed items, use the real WordPress post author if the meta author does not exist
        if ($post->post_type !== 'wprss_feed_item' && empty($meta[static::AUTHOR_KEY])) {
            $wrapperData[static::AUTHOR_KEY] = get_the_author_meta('display_name', $post->post_author);
        }

        // Create a copy of the wrapper data with all the values replaced with `true`
        // This will be used as the override map for the merged data set
        $overrides = array_map('__return_true', $wrapperData);

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

    /**
     * Retrieves the featured image URL for the feed item.
     *
     * @since 4.14
     *
     * @param WP_Post $post The post for the feed item.
     *
     * @return string|null The URL of the featured image or null if the item has no featured image.
     */
    protected function getFtImageUrl($post)
    {
        // Fetch the featured image first
        $attachment = wp_get_attachment_image_url(get_post_thumbnail_id($post->ID), '');
        if ($attachment !== false) {
            return $attachment;
        }

        // Then try the old E&T meta key
        $etThumbnail = get_post_meta($post->ID, 'wprss_item_thumbnail', true);
        if (!empty($etThumbnail)) {
            return $etThumbnail;
        }

        return null;
    }

    /**
     * Retrieves the item URL to use for a feed item.
     *
     * @since 4.14
     *
     * @param WP_Post          $post   The post object.
     * @param DataSetInterface $meta   The meta data set.
     * @param DataSetInterface $source The feed source data set.
     *
     * @return string
     */
    protected function getItemUrl($post, $meta, $source)
    {
        // If not a WPRSS feed item, use its WordPress post permalink
        if ($post->post_type !== 'wprss_feed_item') {
            return get_permalink($post);
        }

        // Use the enclosure if the feed source option is enabled and the item has an enclosure link
        if (!empty($source['enclosure']) && filter_var($source['enclosure'], FILTER_VALIDATE_BOOLEAN) && !empty($meta['enclosure'])) {
            return $meta['enclosure'];
        }

        // Use the permalink in the meta data
        return $meta['permalink'];
    }
}
