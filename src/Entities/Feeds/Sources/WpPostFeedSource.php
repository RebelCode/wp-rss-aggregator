<?php

namespace RebelCode\Wpra\Core\Entities\Feeds\Sources;

use OutOfRangeException;
use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpCptDataSet;
use SimplePie;
use WP_Post;

/**
 * Model class for posts of the WP RSS Aggregator feed source custom post type.
 *
 * @since 4.13
 */
class WpPostFeedSource extends WpCptDataSet
{
    /**
     * The meta prefix.
     *
     * @since 4.13
     */
    const META_PREFIX = 'wprss_';

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param int|string|WP_Post $post The post instance or ID.
     *
     * @throws OutOfRangeException If the post does not exist.
     */
    public function __construct($post)
    {
        parent::__construct($post, static::META_PREFIX, ['ID', 'post_title']);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function createPostDataSet($postOrId)
    {
        return new AliasingDataSet(parent::createPostDataSet($postOrId), [
            'id' => 'ID',
            'title' => 'post_title'
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.14
     */
    protected function createMetaDataSet($postOrId)
    {
        $meta = parent::createMetaDataSet($postOrId);
        $url = isset($meta['url']) ? $meta['url'] : '';

        if (!isset($meta['site_url']) && ($feed = wprss_fetch_feed($url)) instanceof SimplePie) {
            $meta['site_url'] = $feed->get_permalink();
            $meta['feed_image'] = $feed->get_image_url();
        } else {
            $meta['site_url'] = $url;
            $meta['feed_image'] = '';
        }

        $defaults = $this->getDefaultMetaData($meta);
        $fullMeta = new MergedDataSet($meta, $defaults);

        return new MergedDataSet($fullMeta, new ArrayDataSet([
            'def_ft_image_id' => $defFtImage = get_post_thumbnail_id($postOrId),
            'def_ft_image_url' => wp_get_attachment_image_url($defFtImage, ''),
        ]));
    }

    /**
     * Retrieves the default meta data.
     *
     * @since 4.14
     *
     * @param DataSetInterface $meta The existing meta data.
     *
     * @return ArrayDataSet The data set containing the default meta data.
     */
    protected function getDefaultMetaData($meta)
    {
        return new ArrayDataSet([
            'import_source' => '0',
            'import_ft_images' => '',
            'download_images' => '0',
            'siphon_ft_image' => '0',
            'must_have_ft_image' => '0',
            'image_min_width' => 150,
            'image_min_height' => 150,
        ]);
    }
}
