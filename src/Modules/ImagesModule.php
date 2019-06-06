<?php

namespace RebelCode\Wpra\Core\Modules;

use Aventura\Wprss\Core\Caching\ImageCache;
use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Feeds\Models\WpPostFeedItem;
use RebelCode\Wpra\Core\Importer\Images\FbImageContainer;
use RebelCode\Wpra\Core\Importer\Images\ImageContainer;
use RebelCode\Wpra\Core\Modules\Handlers\Images\RemoveFtImageMetaBoxHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterMetaBoxHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RenderTemplateHandler;
use WPRSS_Help;

/**
 * The module that adds image importing functionality to WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class ImagesModule implements ModuleInterface
{
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            /*
             * The image cache manager instance.
             *
             * @since [*next-version*]
             */
            'wpra/images/cache' => function (ContainerInterface $c) {
                $cache = new ImageCache();
                $cache->set_ttl($c->get('wpra/images/cache/ttl'));

                return $cache;
            },
            /*
             * The time-to-live, in seconds, for cached images.
             *
             * @since [*next-version*]
             */
            'wpra/images/cache/ttl' => function () {
                return WEEK_IN_SECONDS;
            },
            /*
             * The container for remote images.
             *
             * @since [*next-version*]
             */
            'wpra/images/container' => function (ContainerInterface $c) {
                return new ImageContainer(
                    $c->get('wpra/images/cache'),
                    $c->get('wpra/logging/logger')
                );
            },
            /*
             * The handler that registers the feed sources images meta box.
             *
             * @since [*next-version*]
             */
            'wpra/images/feeds/meta_box/handler/register' => function (ContainerInterface $c) {
                return new RegisterMetaBoxHandler(
                    'wpra-images',
                    __('Images', 'wprss'),
                    $c->get('wpra/images/feeds/meta_box/handler/render'),
                    'wprss_feed'
                );
            },
            /*
             * The handler that renders the feed sources image options meta box.
             *
             * @since [*next-version*]
             */
            'wpra/images/feeds/meta_box/handler/render' => function (ContainerInterface $c) {
                return new RenderTemplateHandler(
                    $c->get('wpra/images/feeds/meta_box/template'),
                    $c->get('wpra/images/feeds/meta_box/template/context'),
                    true
                );
            },
            /*
             * The template for the feed sources image options meta box.
             *
             * @since [*next-version*]
             */
            'wpra/images/feeds/meta_box/template' => function (ContainerInterface $c) {
                return $c->get('wpra/twig/collection')['admin/feeds/images-meta-box.twig'];
            },
            /*
             * The context for the feed sources image options meta box template.
             *
             * @since [*next-version*]
             */
            'wpra/images/feeds/meta_box/template/context' => function (ContainerInterface $c) {
                return function () use ($c) {
                    global $post;

                    $collection = $c->get('wpra/feeds/sources/collection');
                    $feed = isset($collection[$post->ID])
                        ? $collection[$post->ID]
                        : [];

                    return [
                        'feed' => $feed,
                        'options' => $c->get('wpra/images/feeds/meta_box/template/enabled_options'),
                    ];
                };
            },
            /*
             * The image options that should be shown in the feed sources image meta box.
             *
             * @since [*next-version*]
             */
            'wpra/images/feeds/meta_box/template/enabled_options' => function (ContainerInterface $c) {
                return [
                    'featured_image' => true,
                    'image_min_size' => true,
                ];
            },
            /*
             * The handler that replaces the WordPress featured image meta box with a custom one.
             * This custom meta box changes the terminology from "Featured image" to "Default Featured Image".
             *
             * @since [*next-version*]
             */
            'wpra/images/ft_image/meta_box/handler' => function (ContainerInterface $c) {
                return new RemoveFtImageMetaBoxHandler();
            },
            /*
             * The help tooltips for the feed sources images meta box.
             *
             * @since [*next-version*]
             */
            'wpra/images/feeds/meta_box/tooltips' => function () {
                return [
                    'ft_image' => __(
                        'This option allows you to select which feed item image to use as the featured image. WordPress
                        requires that featured images exist in the media library, so featured images are always
                        downloaded by WP RSS Aggregator.',
                        'wprss'
                    ),
                ];
            },
            /*
             * The handler for the developer images meta box for feed items.
             *
             * @since [*next-version*]
             */
            'wpra/images/items/dev_meta_box/handler' => function (ContainerInterface $c) {
                $templates = $c->get('wpra/twig/collection');

                return new RegisterMetaBoxHandler(
                    'wpra-dev-items-images',
                    __('Images', 'wprss'),
                    new RenderTemplateHandler($templates['admin/items/images-meta-box.twig'], function () {
                        global $post;
                        return [
                            'item' => new WpPostFeedItem($post),
                        ];
                    }, true),
                    'wprss_feed_item'
                );
            }
        ];
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return [
            /*
             * Adds featured image support to feed sources.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/sources/cpt/args' => function (ContainerInterface $c, $args) {
                $args['supports'][] = 'thumbnail';

                return $args;
            },
            /*
             * Adds featured image support to feed items.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/cpt/args' => function (ContainerInterface $c, $args) {
                $args['supports'][] = 'thumbnail';

                return $args;
            },
            /*
             * Decorates the images container to be able to fetch large versions of Facebook images.
             *
             * @since [*next-version*]
             */
            'wpra/images/container' => function (ContainerInterface $c, $container) {
                return new FbImageContainer($container);
            },
        ];
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
        // The handler that registers the images meta box, if Feed to Post's version is not being used
        if (!class_exists('WPRSS_FTP_Meta')) {
            add_action('add_meta_boxes', $c->get('wpra/images/feeds/meta_box/handler/register'));
        }

        // The handler that renders a custom featured image meta box, for the default featured image
        add_action('add_meta_boxes', $c->get('wpra/images/ft_image/meta_box/handler'));

        // Show the developer images meta box for feed items, if the developer filter is enabled
        if (apply_filters('wpra_dev_mode', false) === true) {
            add_action('add_meta_boxes', $c->get('wpra/images/items/dev_meta_box/handler'));
        }

        // Register the meta box tooltips
        $tooltips = $c->get('wpra/images/feeds/meta_box/tooltips');
        add_action('admin_init', function () use ($tooltips) {
            WPRSS_Help::get_instance()->add_tooltips($tooltips);
        });
    }
}
