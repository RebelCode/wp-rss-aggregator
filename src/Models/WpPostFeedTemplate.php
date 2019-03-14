<?php

namespace RebelCode\Wpra\Core\Models;

use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\WpCptDataSet;
use WP_Post;

/**
 * A feed template model implementation for standard WP RSS Aggregator templates that are stored as a CPT.
 *
 * @since [*next-version*]
 */
class WpPostFeedTemplate extends WpCptDataSet
{
    /**
     * The meta prefix.
     *
     * @since [*next-version*]
     */
    const META_PREFIX = 'wprss_template_';

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $postOrId   The post instance or ID.
     */
    public function __construct($postOrId)
    {
        parent::__construct($postOrId, static::META_PREFIX, $this->getPostDataMask());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createPostDataSet($postOrId)
    {
        return new AliasingDataSet(
            parent::createPostDataSet($postOrId),
            [
                'id' => 'ID',
                'name' => 'post_title',
                'slug' => 'post_name',
            ]
        );
    }

    /**
     * Retrieves the list of post fields to retain in the dataset.
     *
     * @since [*next-version*]
     *
     * @return string[]
     */
    protected function getPostDataMask()
    {
        return ['ID', 'post_title', 'post_name'];
    }
}
