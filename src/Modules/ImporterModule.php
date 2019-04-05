<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Feeds\ImportedItemsCollection;

/**
 * The WP RSS Aggregator importer module.
 *
 * @since [*next-version*]
 */
class ImporterModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            'wpra/importer/items/collection' => function () {
                return new ImportedItemsCollection([
                    'relation' => 'AND',
                    [
                        'key' => 'wprss_feed_id',
                        'compare' => 'EXISTS',
                    ],
                ]);
            },
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
    }
}
