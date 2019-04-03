<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Wp\WpRolesProxy;

/**
 * A module that provides various WordPress components as services.
 *
 * @since [*next-version*]
 */
class WpModule implements ModuleInterface
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
             * The WordPress global wpdb instance.
             *
             * @since [*next-version*]
             */
            'wp/db' => function () {
                global $wpdb;

                return $wpdb;
            },
            /*
             * The WordPress user roles manager instance.
             *
             * @since [*next-version*]
             */
            'wp/roles' => function () {
                return new WpRolesProxy();
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
