<?php

namespace RebelCode\Wpra\Core\Query;

use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Feeds\Models\WpPostFeedItem;
use WP_Post;

/**
 * A query iterator implementation for retrieving imported feed items.
 *
 * @since 4.13
 */
class FeedItemsQueryIterator extends AbstractWpQueryIterator
{
    /**
     * An array of feed source IDs to limit the query to.
     *
     * @since 4.13
     *
     * @var int[]|string[]
     */
    protected $sources;

    /**
     * An array of feed source IDs to exclude from the query.
     *
     * @since 4.13
     *
     * @var int[]|string[]
     */
    protected $excludes;

    /**
     * Optional number of items to return, or null to return all items.
     *
     * @since 4.13
     *
     * @var int|null
     */
    protected $numItems;

    /**
     * Optional index of the results page to return or null to get the first page.
     *
     * @since 4.13
     *
     * @var int|null
     */
    protected $page;

    /**
     * A callable that receives a WP Post object and returns a feed item data set.
     *
     * @see   WP_Post
     * @see   DataSetInterface
     *
     * @since 4.13
     *
     * @var callable
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @see   WP_Post
     * @see   DataSetInterface
     *
     * @param array         $sources  An array of feed source IDs to limit the query to.
     * @param array         $excludes An array of feed source IDs to exclude from the query.
     * @param int|null      $numItems Optional number of items to return, or null to return all items.
     * @param int|null      $page     Optional index of the results page to return or null to get the first page.
     * @param callable|null $factory  Optional callable that receives a WP Post object and returns a feed item instance.
     *                                If null, defaults to {@link FeedItem::getFactory()}.
     */
    public function __construct(
        array $sources = [],
        array $excludes = [],
        $numItems = null,
        $page = null,
        callable $factory = null
    ) {
        $this->sources = $sources;
        $this->excludes = $excludes;
        $this->numItems = $numItems;
        $this->page = $page;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function current()
    {
        $post = parent::current();
        $item = ($this->factory !== null)
            ? call_user_func_array($this->factory, [$post])
            : new WpPostFeedItem($post);

        return $item;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getQueryArgs()
    {
        $numItems = ($this->numItems !== null)
            ? (int) $this->numItems
            : -1;
        $paged = ($this->page !== null)
            ? (int) $this->page
            : 1;

        $query = [
            'post_type' => get_post_types([], 'names'),
            'orderby' => 'date',
            'order' => 'DESC',
            'suppress_filters' => true,
            'posts_per_page' => $numItems,
            'paged' => $paged,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'wprss_feed_id',
                    'type' => 'numeric',
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        if (!empty($this->sources)) {
            $query['meta_query'][] = [
                'key' => 'wprss_feed_id',
                'value' => $this->sources,
                'compare' => 'IN',
            ];
        }

        if (!empty($this->excludes)) {
            $query['meta_query'][] = [
                'key' => 'wprss_feed_id',
                'value' => $this->excludes,
                'compare' => 'NOT IN',
            ];
        }

        return $query;
    }
}
