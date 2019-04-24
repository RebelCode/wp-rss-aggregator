<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Wp\WpRolesProxy;

/**
 * A module that provides various WordPress components as services.
 *
 * @since 4.13
 */
class WpModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function getFactories()
    {
        return [
            /*
             * The WordPress global wpdb instance.
             *
             * @since 4.13
             */
            'wp/db' => function () {
                global $wpdb;

                return $wpdb;
            },
            /*
             * The WordPress user roles manager instance.
             *
             * @since 4.13
             */
            'wp/roles' => function () {
                return new WpRolesProxy();
            }
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
