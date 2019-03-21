<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\FeedSources\RenderFeedSourceContentHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterCptHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RenderTemplateHandler;

/**
 * The feed sources module for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class FeedSourcesModule implements ModuleInterface
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
             * The name of the feed sources CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/sources/cpt_name' => function () {
                return 'wprss_feed';
            },
            /*
             * The labels for the feed sources CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/sources/cpt_labels' => function () {
                return [
                    'name' => __('Feed Sources', 'wprss'),
                    'singular_name' => __('Feed Source', 'wprss'),
                    'add_new' => __('Add New', 'wprss'),
                    'all_items' => __('Feed Sources', 'wprss'),
                    'add_new_item' => __('Add New Feed Source', 'wprss'),
                    'edit_item' => __('Edit Feed Source', 'wprss'),
                    'new_item' => __('New Feed Source', 'wprss'),
                    'view_item' => __('View Feed Source', 'wprss'),
                    'search_items' => __('Search Feeds', 'wprss'),
                    'not_found' => __('No Feed Sources Found', 'wprss'),
                    'not_found_in_trash' => __('No Feed Sources Found In Trash', 'wprss'),
                    'menu_name' => __('RSS Aggregator', 'wprss'),
                ];
            },
            /*
             * The capability for the feed sources CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/sources/cpt_capability' => function () {
                return 'feed';
            },
            /*
             * The full arguments for the feed sources CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/sources/cpt_args' => function (ContainerInterface $c) {
                return [
                    'exclude_from_search' => true,
                    'publicly_queryable' => false,
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => true,
                    'public' => true,
                    'show_ui' => true,
                    'query_var' => 'feed_source',
                    'menu_position' => 100,
                    'show_in_menu' => true,
                    'rewrite' => [
                        'slug' => 'feeds',
                        'with_front' => false,
                    ],
                    'capability_type' => $c->get('wpra/feeds/sources/cpt_capability'),
                    'map_meta_cap' => true,
                    'supports' => ['title'],
                    'labels' => $c->get('wpra/feeds/sources/cpt_labels'),
                    'menu_icon' => 'dashicons-rss',
                ];
            },
            /*
             * The handler that registers the feed sources CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/sources/register_cpt_handler' => function (ContainerInterface $c) {
                return new RegisterCptHandler(
                    $c->get('wpra/feeds/sources/cpt_name'),
                    $c->get('wpra/feeds/sources/cpt_args')
                );
            },
            /*
             * The handler that renders a feed source's content on the front-end.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/sources/render_content_handler' => function (ContainerInterface $c) {
                return new RenderFeedSourceContentHandler($c->get('wpra/templates/feeds/master_template'));
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
        add_action('init', $c->get('wpra/feeds/sources/register_cpt_handler'));
        add_filter('the_content', $c->get('wpra/feeds/sources/render_content_handler'));
    }
}
