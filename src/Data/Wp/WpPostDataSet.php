<?php

namespace RebelCode\Wpra\Core\Data\Wp;

use OutOfRangeException;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Util\NormalizeWpPostCapableTrait;
use WP_Post;

/**
 * An implementation of a data set that acts as a wrapper for a WordPress post.
 *
 * @since 4.13
 */
class WpPostDataSet extends MergedDataSet
{
    /* @since 4.13 */
    use NormalizeWpPostCapableTrait;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param int|string|WP_Post $postOrId The WordPress post instance or post ID.
     *
     * @throws OutOfRangeException If the post does not exist.
     */
    public function __construct($postOrId)
    {
        $post = $this->normalizeWpPost($postOrId);

        parent::__construct($this->createPostDataSet($post), $this->createMetaDataSet($post));
    }

    /**
     * Creates the data set for the post data.
     *
     * @since 4.13
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
     * @since 4.13
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
