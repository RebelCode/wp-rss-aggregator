<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\WpArrayOptionDataSet;

/**
 * The settings module for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class SettingsModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            'wpra/settings/option_name' => function (ContainerInterface $c) {
                return 'wprss_settings_general';
            },
            'wpra/settings/dataset' => function (ContainerInterface $c) {
                return new MergedDataSet(
                    new WpArrayOptionDataSet($c->get('wpra/settings/option_name')),
                    new ArrayDataSet(wprss_get_default_settings_general())
                );
            }
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
