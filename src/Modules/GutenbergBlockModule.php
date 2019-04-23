<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\Handlers\GutenbergBlock\FetchFeedSourcesHandler;
use RebelCode\Wpra\Core\Modules\Handlers\GutenbergBlock\GutenbergBlockAssetsHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterGutenbergBlockHandler;

/**
 * The Gutenberg block for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class GutenbergBlockModule implements ModuleInterface
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
             * The Gutenberg block name.
             *
             * @since [*next-version*]
             */
            'wpra/gutenberg_block/name' => function (ContainerInterface $c) {
                return 'wpra-shortcode/wpra-shortcode';
            },

            /*
             * Available Gutenberg block attributes.
             *
             * @since [*next-version*]
             */
            'wpra/gutenberg_block/attributes' => function (ContainerInterface $c) {
                return [
                    'isAll' => [
                        'type' => 'boolean',
                        'default' => true,
                    ],
                    'template' => [
                        'type' => 'string',
                        'default' => 'default',
                    ],
                    'pagination_enabled' => [
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
                    'className' => [
                        'type' => 'string'
                    ],
                ];
            },

            /*
             * The Gutenberg block configuration.
             *
             * @since [*next-version*]
             */
            'wpra/gutenberg_block/config' => function (ContainerInterface $c) {
                return [
                    'attributes' => $c->get('wpra/gutenberg_block/attributes'),
                    'render_callback' => $c->get('wpra/shortcode/feeds/handler')
                ];
            },

            /*
             * The Gutenberg block configuration.
             *
             * @since [*next-version*]
             */
            'wpra/gutenberg_block/handlers/register' => function (ContainerInterface $c) {
                return new RegisterGutenbergBlockHandler(
                    $c->get('wpra/gutenberg_block/name'),
                    $c->get('wpra/gutenberg_block/config')
                );
            },

            /*
             * The Gutenberg block assets handler.
             *
             * @since [*next-version*]
             */
            'wpra/gutenberg_block/handlers/assets' => function (ContainerInterface $c) {
                return new GutenbergBlockAssetsHandler(
                    $c->get('wpra/templates/feeds/collection')
                );
            },

            /*
             * The handler for retrieving feed sources in Gutenberg block.
             *
             * @since [*next-version*]
             */
            'wpra/gutenberg_block/handlers/fetch_feed_sources' => function (ContainerInterface $c) {
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
        call_user_func($c->get('wpra/gutenberg_block/handlers/register'));

        add_action('enqueue_block_editor_assets', $c->get('wpra/gutenberg_block/handlers/assets'));

        add_action('wp_ajax_wprss_fetch_items', $c->get('wpra/gutenberg_block/handlers/fetch_feed_sources'));
    }
}
