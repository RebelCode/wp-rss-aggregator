<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\Handlers\CustomFeed\RegisterCustomFeedHandler;
use RebelCode\Wpra\Core\Modules\Handlers\CustomFeed\RenderCustomFeedHandler;

/**
 * The module for the WP RSS Aggregator custom feed.
 *
 * @since [*next-version*]
 */
class CustomFeedModule implements ModuleInterface
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
             * The default custom feed URL.
             *
             * @since [*next-version*]
             */
            'wpra/custom_feed/default_url' => function () {
                return 'wprss';
            },
            /*
             * The handler that registers the custom feed.
             *
             * @since [*next-version*]
             */
            'wpra/custom_feed/register_handler' => function (ContainerInterface $c) {
                return new RegisterCustomFeedHandler(
                    $c->get('wpra/settings/dataset'),
                    $c->get('wpra/custom_feed/default_url'),
                    $c->get('wpra/custom_feed/render_handler')
                );
            },
            /*
             * The handler that renders the custom feed.
             *
             * @since [*next-version*]
             */
            'wpra/custom_feed/render_handler' => function () {
                return new RenderCustomFeedHandler();
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
        add_action('init', $c->get('wpra/custom_feed/register_handler'));
    }
}
