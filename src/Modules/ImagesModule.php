<?php

namespace RebelCode\Wpra\Core\Modules;

use Aventura\Wprss\Core\Caching\ImageCache;
use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Entities\Feeds\Items\WpPostFeedItem;
use RebelCode\Wpra\Core\Handlers\Images\DeleteImagesHandler;
use RebelCode\Wpra\Core\Handlers\Images\RemoveFtImageMetaBoxHandler;
use RebelCode\Wpra\Core\Handlers\RegisterMetaBoxHandler;
use RebelCode\Wpra\Core\Handlers\RenderTemplateHandler;
use RebelCode\Wpra\Core\Importer\Images\FbImageContainer;
use RebelCode\Wpra\Core\Importer\Images\ImageContainer;
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
             * The flag that controls whether UI for controlling image importing is enabled or not.
             *
             * @since [*next-version*]
             */
            'wpra/images/ui_enabled' => function (ContainerInterface $c) {
                return false;
            },
            /*
             * Whether the feature to import featured images is enabled or not.
             *
             * @since [*next-version*]
             */
            'wpra/images/features/import_ft_images' => function () {
                return true;
            },
            /*
             * Whether the feature to restrict images by size is enabled or not.
             *
             * @since [*next-version*]
             */
            'wpra/images/features/image_min_size' => function () {
                return true;
            },
            /*
             * Whether the feature to download non-featured images is enabled or not.
             *
             * @since [*next-version*]
             */
            'wpra/images/features/download_images' => function () {
                return false;
            },
            /*
             * Whether the feature to remove featured images from the content is enabled or not.
             *
             * @since [*next-version*]
             */
            'wpra/images/features/siphon_ft_image' => function () {
                return false;
            },
            /*
             * Whether the feature to reject items without featured images is enabled or not.
             *
             * @since [*next-version*]
             */
            'wpra/images/features/must_have_ft_image' => function () {
                return true;
            },
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
                    'import_ft_images' => $c->get('wpra/images/features/import_ft_images'),
                    'image_min_size' => $c->get('wpra/images/features/image_min_size'),
                    'download_images' => $c->get('wpra/images/features/download_images'),
                    'siphon_ft_image' => $c->get('wpra/images/features/siphon_ft_image'),
                    'must_have_ft_image' => $c->get('wpra/images/features/must_have_ft_image'),
                ];
            },
            /*
             * The handler that removes the WordPress featured image meta box.
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
                        "This option allows you to select which feed item image to use as the featured image. Automatic best image detection will attempt to find the largest image with the best aspect ratio.

                        WordPress requires that featured images exist in the media library, so WP RSS Aggregator will always download and save featured images.",
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
            },
            /*
             * The handler that deletes an imported item's featured image when the item is deleted.
             *
             * @since [*next-version*]
             */
            'wpra/images/items/handlers/delete_images' => function (ContainerInterface $c) {
                return new DeleteImagesHandler(
                    $c->get('wpra/importer/items/collection')
                );
            },
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
        // The handler that removes the WordPress featured image meta box
        add_action('add_meta_boxes', $c->get('wpra/images/ft_image/meta_box/handler'));

        // Check if the images UI is enabled
        if ($c->get('wpra/images/ui_enabled')) {
            // The handler that registers the images meta box, if Feed to Post's version is not being used
            if (!class_exists('WPRSS_FTP_Meta')) {
                add_action('add_meta_boxes', $c->get('wpra/images/feeds/meta_box/handler/register'));
            }

            // Show the developer images meta box for feed items, if the developer filter is enabled
            if (apply_filters('wpra_dev_mode', false) === true) {
                add_action('add_meta_boxes', $c->get('wpra/images/items/dev_meta_box/handler'));
            }

            // Register the meta box tooltips
            $tooltips = $c->get('wpra/images/feeds/meta_box/tooltips');
            add_action('admin_init', function () use ($tooltips) {
                WPRSS_Help::get_instance()->add_tooltips($tooltips);
            });

            // The handler that deletes images when the respective imported item is deleted
            add_action('before_delete_post', $c->get('wpra/images/items/handlers/delete_images'));
        }
    }
}
