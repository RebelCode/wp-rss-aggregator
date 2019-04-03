<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;

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
            'wpdb' => function (ContainerInterface $c) {
                global $wpdb;

                return $wpdb;
            },
            /*
             * The WordPress user roles manager instance.
             *
             * @since [*next-version*]
             */
            'wp/roles' => function () {
                global $wp_roles;

                return $wp_roles;
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
