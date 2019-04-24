<?php

namespace RebelCode\Wpra\Core\Feeds\Models;

use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpCptDataSet;
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
}
