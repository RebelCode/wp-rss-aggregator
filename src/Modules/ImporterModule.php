<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Feeds\ImportedItemsCollection;

/**
 * The WP RSS Aggregator importer module.
 *
 * @since 4.13
 */
class ImporterModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function getFactories()
    {
        return [
            'wpra/importer/items/collection' => function () {
                return new ImportedItemsCollection(
                    [
                        'relation' => 'AND',
                        [
                            'key' => 'wprss_feed_id',
                            'compare' => 'EXISTS',
                        ],
                    ],
                    [
                        'order_by' => 'date',
                        'order'    => 'DESC',
                    ]
                );
            },
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function getExtensions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function run(ContainerInterface $c)
    {
    }
}
