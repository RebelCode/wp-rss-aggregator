<?php

namespace RebelCode\Wpra\Core\Templates;

use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\PostMetaDataSet;
use WP_Post;

/**
 * A feed template model implementation for standard WP RSS Aggregator templates that are stored as a CPT.
 *
 * @since [*next-version*]
 */
class WpPostFeedTemplate extends PostMetaDataSet
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
     * @param int|string|WP_Post $post The post ID or {@link WP_Post} instance.
     */
    public function __construct($post)
    {
        $post = ($post instanceof WP_Post) ? $post : get_post($post);

        $parent = new ArrayDataSet([
            'id' => $post->ID,
            'name' => $post->post_title,
            'slug' => $post->post_name,
            'template_type' => 'list',
        ]);

        parent::__construct($post->ID, static::META_PREFIX, [], $parent);
    }
}
