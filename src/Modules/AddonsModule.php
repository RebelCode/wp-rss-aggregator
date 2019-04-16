<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;

/**
 * The module that contains addon-related services.
 *
 * @since [*next-version*]
 */
class AddonsModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            /*
             * The list of WP RSS Aggregator addons.
             *
             * @since [*next-version*]
             */
            'wpra/addons' => function () {
                return [];
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
