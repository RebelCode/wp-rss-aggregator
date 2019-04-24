<?php

namespace RebelCode\Wpra\Core\Templates\Feeds\Models;

use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpCptDataSet;
use WP_Post;

/**
 * A feed template model implementation for standard WP RSS Aggregator templates that are stored as a CPT.
 *
 * @since 4.13
 */
class WpPostFeedTemplate extends WpCptDataSet
{
    /**
     * The meta prefix.
     *
     * @since 4.13
     */
    const META_PREFIX = 'wprss_template_';

    /**
     * Constructor.
     *
     * @since 4.13
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
     * @since 4.13
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
     * @since 4.13
     *
     * @return string[]
     */
    protected function getPostDataMask()
    {
        return ['ID', 'post_title', 'post_name'];
    }
}
