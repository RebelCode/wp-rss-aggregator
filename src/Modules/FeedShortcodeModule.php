<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterShortcodeHandler;
use RebelCode\Wpra\Core\Shortcodes\FeedsShortcode;

/**
 * The feeds shortcode for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class FeedShortcodeModule implements ModuleInterface
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
             * The shortcode handler.
             *
             * @since [*next-version*]
             */
            'wpra/shortcode/feeds/handler' => function (ContainerInterface $c) {
                return new FeedsShortcode(
                    $c->get('wpra/templates/feeds/master_template')
                );
            },
            /*
             * The shortcode names.
             *
             * @since [*next-version*]
             */
            'wpra/shortcode/feeds/names' => function (ContainerInterface $c) {
                return [
                    'wp_rss_aggregator',
                    'wp-rss-aggregator',
                ];
            },
            'wpra/shortcode/feeds/handlers/register' => function (ContainerInterface $c) {
                new RegisterShortcodeHandler(
                    $c->get('wpra/shortcode/feeds/names'),
                    $c->get('wpra/shortcode/feeds/handler')
                );
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
        add_action('plugins_loaded', $c->get('wpra/shortcode/feeds/handlers/register'));
    }
}
