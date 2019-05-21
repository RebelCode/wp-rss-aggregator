<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\Handlers\GutenbergBlock\FetchFeedSourcesHandler;
use RebelCode\Wpra\Core\Modules\Handlers\GutenbergBlock\GutenbergBlockAssetsHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterGutenbergBlockHandler;
use RebelCode\Wpra\Core\Wp\Asset\ScriptAsset;
use RebelCode\Wpra\Core\Wp\Asset\StyleAsset;
use RebelCode\Wpra\Core\Wp\ScriptState;

/**
 * The Gutenberg block for WP RSS Aggregator.
 *
 * @since 4.13
 */
class GutenbergBlockModule implements ModuleInterface
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
             * The Gutenberg block name.
             *
             * @since 4.13
             */
            'wpra/gutenberg_block/name' => function (ContainerInterface $c) {
                return 'wpra-shortcode/wpra-shortcode';
            },

            /*
             * Available Gutenberg block attributes.
             *
             * @since 4.13
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
                    'className' => [
                        'type' => 'string'
                    ],
                ];
            },

            /*
             * The Gutenberg block configuration.
             *
             * @since 4.13
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
             * @since 4.13
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
             * @since 4.13
             */
            'wpra/gutenberg_block/handlers/assets' => function (ContainerInterface $c) {
                return new GutenbergBlockAssetsHandler(
                    $c->get('wpra/gutenberg_block/assets_list'),
                    $c->get('wpra/gutenberg_block/states')
                );
            },

            /*
             * The list of the block's assets.
             *
             * @since [*next-version*]
             */
            'wpra/gutenberg_block/assets_list' => function (ContainerInterface $c) {
                return [
                    'gutenberg_script' => $c->get('wpra/scripts/gutenberg'),
                    'gutenberg_style' => $c->get('wpra/styles/gutenberg'),
                ];
            },

            /*
             * The list of the block's states.
             *
             * @since [*next-version*]
             */
            'wpra/gutenberg_block/states' => function (ContainerInterface $c) {
                return [
                    'main' => $c->get('wpra/states/gutenberg'),
                ];
            },

            /*
             * The script state for gutenberg block.
             *
             * @since [*next-version*]
             */
            'wpra/states/gutenberg' => function (ContainerInterface $c) {
                return new ScriptState('wpra-gutenberg-block', 'WPRA_BLOCK', function () use ($c) {
                    return $c->get('wpra/states/raw/gutenberg');
                });
            },

            /*
             * Raw gutenberg state.
             *
             * @since [*next-version*]
             */
            'wpra/states/raw/gutenberg' => function (ContainerInterface $c) {
                $templatesCollection = $c->get('wpra/templates/feeds/collection');
                $templates = [];
                foreach ($templatesCollection as $template) {
                    $templates[] = [
                        'label' => $template['name'],
                        'value' => $template['slug'],
                        'limit' => isset($template['options']['limit']) ? $template['options']['limit'] : 15,
                        'pagination' => isset($template['options']['pagination']) ? $template['options']['pagination'] : true,
                    ];
                }
                return [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'templates' => $templates,
                    'is_et_active' => wprss_is_et_active(),
                ];
            },

            /*
             * The block's script.
             *
             * @since [*next-version*]
             */
            'wpra/scripts/gutenberg' => function (ContainerInterface $c) {
                $script = new ScriptAsset('wpra-gutenberg-block', WPRSS_APP_JS . 'gutenberg-block.min.js');
                return $script->setDependencies([
                    'wp-blocks',
                    'wp-i18n',
                    'wp-element',
                    'wp-editor',
                ]);
            },

            /*
             * The block's style.
             *
             * @since [*next-version*]
             */
            'wpra/styles/gutenberg' => function (ContainerInterface $c) {
                return new StyleAsset('wpra-gutenberg-block', WPRSS_APP_CSS . 'gutenberg-block.min.css');
            },

            /*
             * The handler for retrieving feed sources in Gutenberg block.
             *
             * @since 4.13
             */
            'wpra/gutenberg_block/handlers/fetch_feed_sources' => function (ContainerInterface $c) {
                return new FetchFeedSourcesHandler();
            },
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
        call_user_func($c->get('wpra/gutenberg_block/handlers/register'));

        add_action('enqueue_block_editor_assets', $c->get('wpra/gutenberg_block/handlers/assets'));

        add_action('wp_ajax_wprss_fetch_items', $c->get('wpra/gutenberg_block/handlers/fetch_feed_sources'));
    }
}
