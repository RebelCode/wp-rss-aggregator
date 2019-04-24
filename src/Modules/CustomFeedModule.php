<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Modules\Handlers\CustomFeed\RegisterCustomFeedHandler;
use RebelCode\Wpra\Core\Modules\Handlers\CustomFeed\RenderCustomFeedHandler;

/**
 * The module for the WP RSS Aggregator custom feed.
 *
 * @since 4.13
 */
class CustomFeedModule implements ModuleInterface
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
             * The default custom feed URL.
             *
             * @since 4.13
             */
            'wpra/custom_feed/default_url' => function () {
                return 'wprss';
            },
            /*
             * The handler that registers the custom feed.
             *
             * @since 4.13
             */
            'wpra/custom_feed/register_handler' => function (ContainerInterface $c) {
                return new RegisterCustomFeedHandler(
                    $c->get('wpra/custom_feed/settings'),
                    $c->get('wpra/custom_feed/default_url'),
                    $c->get('wpra/custom_feed/render_handler')
                );
            },
            /*
             * The settings to use for the custom feed.
             *
             * Resolves to the core plugin's general settings dataset, if available.
             *
             * @since 4.13
             */
            'wpra/custom_feed/settings' => function (ContainerInterface $c) {
                if (!$c->has('wpra/settings/general/dataset')) {
                    return $c->get('wpra/custom_feed/default_settings');
                }

                return $c->get('wpra/settings/general/dataset');
            },
            /*
             * The default settings for the custom feed.
             *
             * @since 4.13
             */
            'wpra/custom_feed/default_settings' => function () {
                return new ArrayDataSet([
                    'custom_feed_url' => 'wprss'
                ]);
            },
            /*
             * The handler that renders the custom feed.
             *
             * @since 4.13
             */
            'wpra/custom_feed/render_handler' => function () {
                return new RenderCustomFeedHandler();
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
        add_action('init', $c->get('wpra/custom_feed/register_handler'));
    }
}
