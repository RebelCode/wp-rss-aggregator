<?php

namespace RebelCode\Wpra\Core\Data;

use RebelCode\Wpra\Core\Util\NormalizeWpPostCapableTrait;
use WP_Post;

/**
 * An implementation of a data set that acts as a wrapper for a WordPress post.
 *
 * @since [*next-version*]
 */
class WpPostDataSet extends MergedDataSet
{
    /* @since [*next-version*] */
    use NormalizeWpPostCapableTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $postOrId The WordPress post instance or post ID.
     */
    public function __construct($postOrId)
    {
        $post = $this->normalizeWpPost($postOrId);

        parent::__construct($this->createPostDataSet($post), $this->createMetaDataSet($post));
    }

    /**
     * Creates the data set for the post data.
     *
     * @since [*next-version*]
     *
     * @param WP_Post $post The WordPress post instance.
     *
     * @return DataSetInterface The created data set.
     */
    protected function createPostDataSet($post)
    {
        return new WpPostDataDataSet($post);
    }

    /**
     * Creates the data set for the post data.
     *
     * @since [*next-version*]
     *
     * @param WP_Post $post The WordPress post instance.
     *
     * @return DataSetInterface The created data set.
     */
    protected function createMetaDataSet($post)
    {
        return new WpPostMetaDataSet($post);
    }
}
