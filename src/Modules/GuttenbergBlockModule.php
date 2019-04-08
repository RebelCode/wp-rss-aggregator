<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\Handlers\GuttenbergBlock\FetchFeedSourcesHandler;
use RebelCode\Wpra\Core\Modules\Handlers\GuttenbergBlock\GuttenbergBlockAssetsHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterGuttenbergBlockHandler;

/**
 * The Guttenberg block for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class GuttenbergBlockModule implements ModuleInterface
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
             * The Guttenberg block name.
             *
             * @since [*next-version*]
             */
            'wpra/guttenberg_block/name' => function (ContainerInterface $c) {
                return 'wpra-shortcode/wpra-shortcode';
            },

            /*
             * Available Guttenberg block attributes.
             *
             * @since [*next-version*]
             */
            'wpra/guttenberg_block/attributes' => function (ContainerInterface $c) {
                return [
                    'isAll' => [
                        'type' => 'boolean',
                        'default' => true,
                    ],
                    'pagination' => [
                        'type' => 'boolean',
                        'default' => true,
                    ],
                    'page' => [
                        'type' => 'number',
                    ],
                    'limit' => [
                        'type' => 'number',
                    ],
                    'exclude' => [
                        'type' => 'string'
                    ],
                    'source' => [
                        'type' => 'string'
                    ],
                ];
            },

            /*
             * The Guttenberg block configuration.
             *
             * @since [*next-version*]
             */
            'wpra/guttenberg_block/config' => function (ContainerInterface $c) {
                return [
                    'attributes' => $c->get('wpra/guttenberg_block/attributes'),
                    'render_callback' => $c->get('wpra/shortcode/feeds/handler')
                ];
            },

            /*
             * The Guttenberg block configuration.
             *
             * @since [*next-version*]
             */
            'wpra/guttenberg_block/handlers/register' => function (ContainerInterface $c) {
                return new RegisterGuttenbergBlockHandler(
                    $c->get('wpra/guttenberg_block/name'),
                    $c->get('wpra/guttenberg_block/config')
                );
            },

            /*
             * The Guttenberg block assets handler.
             *
             * @since [*next-version*]
             */
            'wpra/guttenberg_block/handlers/assets' => function (ContainerInterface $c) {
                return new GuttenbergBlockAssetsHandler();
            },

            /*
             * The handler for retrieving feed sources in Guttenberg block.
             *
             * @since [*next-version*]
             */
            'wpra/guttenberg_block/handlers/fetch_feed_sources' => function (ContainerInterface $c) {
                return new FetchFeedSourcesHandler();
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
        add_action('plugins_loaded', $c->get('wpra/guttenberg_block/handlers/register'));

        add_action('enqueue_block_editor_assets', $c->get('wpra/guttenberg_block/handlers/assets'));

        add_action('wp_ajax_wprss_fetch_items', $c->get('wpra/guttenberg_block/handlers/fetch_feed_sources'));
    }
}
