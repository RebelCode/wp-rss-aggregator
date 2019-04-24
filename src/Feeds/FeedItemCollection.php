<?php

namespace RebelCode\Wpra\Core\Feeds;

use RebelCode\Wpra\Core\Data\Collections\WpPostCollection;
use RebelCode\Wpra\Core\Feeds\Models\WpPostFeedItem;
use WP_Post;

/**
 * A collection implementation that is specific to WP RSS Aggregator feed items.
 *
 * @since 4.13
 */
class FeedItemCollection extends WpPostCollection
{
    /**
     * Constructor.
     *
     * @since 4.13
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
     * @since 4.13
     */
    protected function createModel(WP_Post $post)
    {
        return new WpPostFeedItem($post);
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to ensure that the status is "publish".
     *
     * @since 4.13
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
     * @since 4.13
     */
    protected function handleFilter(&$queryArgs, $key, $value)
    {
        $r = parent::handleFilter($queryArgs, $key, $value);

        if ($key === 'sources') {
            $queryArgs['meta_query']['relation'] = 'AND';
            $queryArgs['meta_query'][] = [
                'key' => 'wprss_feed_id',
                'value' => $this->_normalizeArray($value),
                'compare' => 'IN',
            ];

            return true;
        }

        if ($key === 'exclude') {
            $queryArgs['meta_query']['relation'] = 'AND';
            $queryArgs['meta_query'][] = [
                'key' => 'wprss_feed_id',
                'value' => $this->_normalizeArray($value),
                'compare' => 'NOT IN',
            ];

            return true;
        }

        return $r;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function createSelfWithFilter($filter)
    {
        return new static($this->postType, $filter);
    }
}
