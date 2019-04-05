<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Templates\Feeds\LegacyDisplayTemplate;

/**
 * The feeds display module for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class FeedDisplayModule implements ModuleInterface
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
             * The feed display template.
             *
             * @since [*next-version*]
             */
            'wpra/display/feeds/template' => function (ContainerInterface $c) {
                return $c->get('wpra/display/feeds/legacy_template');
            },
            /*
             * The legacy feed display template used by older versions of WP RSS Aggregator.
             *
             * @since [*next-version*]
             */
            'wpra/display/feeds/legacy_template' => function (ContainerInterface $c) {
                return new LegacyDisplayTemplate();
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
