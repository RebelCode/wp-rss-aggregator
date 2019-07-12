<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Entities\Feeds\Items\ImportedItemsCollection;

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
            'wpra/importer/cache/dir' => function () {
                return get_temp_dir() . 'wprss/simplepie/';
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
