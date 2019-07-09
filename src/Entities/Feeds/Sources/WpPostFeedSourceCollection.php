<?php

namespace RebelCode\Wpra\Core\Entities\Feeds\Sources;

use RebelCode\Wpra\Core\Data\Collections\WpPostCollection;
use WP_Post;

/**
 * A collection implementation that is specific to WP RSS Aggregator feed sources.
 *
 * @since 4.14
 */
class WpPostFeedSourceCollection extends WpPostCollection
{
    /**
     * Constructor.
     *
     * @since @since 4.14
     *
     * @param string     $postType The name of the post type.
     * @param array|null $filter   Optional filter to restrict the collection query.
     */
    public function __construct($postType, $filter = null)
    {
        parent::__construct($postType, [], $filter);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.14
     */
    protected function createModel(WP_Post $post)
    {
        return new WpPostFeedSource($post);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.14
     */
    protected function getBasePostQueryArgs()
    {
        $args = parent::getBasePostQueryArgs();
        $args['post_status'] = 'publish';

        return $args;
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to ensure that the status is "publish".
     *
     * @since 4.14
     */
    protected function getNewPostData($data)
    {
        $post = parent::getNewPostData($data);
        $post['post_status'] = 'publish';

        return $post;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.14
     */
    protected function createSelfWithFilter($filter)
    {
        return new static($this->postType, $filter);
    }
}
