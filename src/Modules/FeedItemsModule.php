<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Feeds\FeedItemCollection;
use RebelCode\Wpra\Core\Modules\Handlers\AddCptMetaCapsHandler;
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
            'wpra/feeds/items/cpt/name' => function () {
                return 'wprss_feed_item';
            },
            /*
             * The labels for the feed items CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/cpt/labels' => function () {
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
             * The capability for the feed items CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/cpt/capability' => function () {
                return 'feed_item';
            },
            /*
             * The user roles that have the feed items CPT capabilities.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/cpt/capability_roles' => function (ContainerInterface $c) {
                // Identical to feed sources
                return $c->get('wpra/feeds/sources/cpt/capability_roles');
            },
            /*
             * The full arguments for the feed items CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/cpt/args' => function (ContainerInterface $c) {
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
                    'capability_type' => $c->get('wpra/feeds/items/cpt/capability'),
                    'map_meta_cap' => true,
                    'labels' => $c->get('wpra/feeds/items/cpt/labels'),
                ];
            },
            /*
             * The collection for feed items.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/collection' => function (ContainerInterface $c) {
                return new FeedItemCollection($c->get('wpra/feeds/items/cpt/name'));
            },
            /*
             * The handler that registers the feed items CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/handlers/register_cpt' => function (ContainerInterface $c) {
                return new RegisterCptHandler(
                    $c->get('wpra/feeds/items/cpt/name'),
                    $c->get('wpra/feeds/items/cpt/args')
                );
            },
            /*
             * The handler that adds the feed items CPT capabilities to the appropriate user roles.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/items/handlers/add_cpt_capabilities' => function (ContainerInterface $c) {
                return new AddCptMetaCapsHandler(
                    $c->get('wp/roles'),
                    $c->get('wpra/feeds/items/cpt/capability_roles'),
                    $c->get('wpra/feeds/items/cpt/capability')
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
        add_action('init', $c->get('wpra/feeds/items/handlers/register_cpt'));
        add_action('admin_init', $c->get('wpra/feeds/items/handlers/add_cpt_capabilities'));
    }
}
