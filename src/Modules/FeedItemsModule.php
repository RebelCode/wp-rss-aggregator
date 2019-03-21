<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterCptHandler;

/**
 * The feed items module for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class FeedItemsModule implements ModuleInterface
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
             * The name of the feed items CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/cpt_name' => function () {
                return 'wprss_feed_item';
            },
            /*
             * The labels for the feed items CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/cpt_labels' => function () {
                return [
                    'name' => __('Feed Items', 'wprss'),
                    'singular_name' => __('Feed Item', 'wprss'),
                    'all_items' => __('Feed Items', 'wprss'),
                    'view_item' => __('View Feed Items', 'wprss'),
                    'search_items' => __('Search Feed Items', 'wprss'),
                    'not_found' => __('No Feed Items Found', 'wprss'),
                    'not_found_in_trash' => __('No Feed Items Found In Trash', 'wprss'),
                ];
            },
            /*
             * The full arguments for the feed items CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/cpt_args' => function (ContainerInterface $c) {
                return [
                    'exclude_from_search' => true,
                    'publicly_queryable' => false,
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => false,
                    'public' => true,
                    'show_ui' => true,
                    'query_var' => 'feed_item',
                    'show_in_menu' => 'edit.php?post_type=wprss_feed',
                    'rewrite' => [
                        'slug' => 'feed-items',
                        'with_front' => false,
                    ],
                    'capability_type' => 'feed_item',
                    'map_meta_cap' => true,
                    'labels' => $c->get('wpra/feeds/items/cpt_labels'),
                ];
            },
            /*
             * The handler that registers the feed items CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/register_cpt_handler' => function (ContainerInterface $c) {
                return new RegisterCptHandler(
                    $c->get('wpra/feeds/items/cpt_name'),
                    $c->get('wpra/feeds/items/cpt_args')
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
        add_action('init', $c->get('wpra/feeds/items/register_cpt_handler'));
    }
}
