<?php

namespace RebelCode\Wpra\Core\Entities\Collections;

use RebelCode\Entities\Api\SchemaInterface;

/**
 * A collection implementation that is specific to WP RSS Aggregator feed items.
 *
 * @since 4.13
 */
class FeedItemCollection extends WpEntityCollection
{
    /**
     * Constructor.
     *
     * @since 4.14
     *
     * @param string          $postType The name of the post type.
     * @param SchemaInterface $schema   The schema for feed item entities.
     */
    public function __construct($postType, SchemaInterface $schema)
    {
        parent::__construct($postType, $schema);
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
}
