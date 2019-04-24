<?php

namespace RebelCode\Wpra\Core\Feeds;

use RuntimeException;

/**
 * A collection implementation for all items imported by WP RSS Aggregator.
 *
 * @since 4.13
 */
class ImportedItemsCollection extends FeedItemCollection
{
    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param array      $metaQuery The meta query.
     * @param array|null $filter    Optional filter to restrict the collection query.
     */
    public function __construct($metaQuery = [], $filter = null)
    {
        parent::__construct(null, $filter);

        $this->metaQuery = $metaQuery;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function set($key, $data)
    {
        throw new RuntimeException('Cannot write to imported items collection');
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getBasePostQueryArgs()
    {
        $args = parent::getBasePostQueryArgs();

        $args['post_type'] = get_post_types();

        return $args;
    }
}
