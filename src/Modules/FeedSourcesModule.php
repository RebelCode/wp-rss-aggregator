<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\Handlers\AddCapabilitiesHandler;
use RebelCode\Wpra\Core\Modules\Handlers\AddCptMetaCapsHandler;
use RebelCode\Wpra\Core\Modules\Handlers\FeedSources\RenderFeedSourceContentHandler;
use RebelCode\Wpra\Core\Modules\Handlers\MultiHandler;
use RebelCode\Wpra\Core\Modules\Handlers\NullHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterCptHandler;
use RebelCode\Wpra\Core\Templates\NullTemplate;

/**
 * The feed sources module for WP RSS Aggregator.
 *
 * @since 4.13
 */
class FeedSourcesModule implements ModuleInterface
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
             * The name of the feed sources CPT.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/cpt/name' => function () {
                return 'wprss_feed';
            },
            /*
             * The labels for the feed sources CPT.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/cpt/labels' => function () {
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
             * @since 4.13
             */
            'wpra/feeds/sources/cpt/capability' => function () {
                return 'feed_source';
            },
            /*
             * The full arguments for the feed sources CPT.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/cpt/args' => function (ContainerInterface $c) {
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
                    'capability_type' => $c->get('wpra/feeds/sources/cpt/capability'),
                    'map_meta_cap' => true,
                    'supports' => ['title'],
                    'labels' => $c->get('wpra/feeds/sources/cpt/labels'),
                    'menu_icon' => 'dashicons-rss',
                ];
            },
            /*
             * The user roles that have the feed sources CPT capabilities.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/cpt/capability_roles' => function () {
                return ['administrator', 'editor'];
            },
            /*
             * The capability for the feed sources CPT admin menu.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/menu/capability' => function () {
                return 'manage_feed_settings';
            },
            /*
             * The user roles that have the feed sources CPT admin menu capabilities.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/menu/capability_roles' => function (ContainerInterface $c) {
                // Identical to CPT roles
                return $c->get('wpra/feeds/sources/cpt/capability_roles');
            },
            /*
             * The handler that registers the feed sources CPT.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/handlers/register_cpt' => function (ContainerInterface $c) {
                return new RegisterCptHandler(
                    $c->get('wpra/feeds/sources/cpt/name'),
                    $c->get('wpra/feeds/sources/cpt/args')
                );
            },
            /*
             * The template used to render feed source content.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/content_template' => function (ContainerInterface $c) {
                if ($c->has('wpra/display/feeds/template')) {
                    return $c->get('wpra/display/feeds/template');
                }

                return new NullTemplate();
            },
            /*
             * The handler that renders a feed source's content on the front-end.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/handlers/render_content' => function (ContainerInterface $c) {
                return new RenderFeedSourceContentHandler($c->get('wpra/feeds/sources/content_template'));
            },
            /*
             * The handler that adds the capability that allows users to see and access the admin menu.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/handlers/add_menu_capabilities' => function (ContainerInterface $c) {
                if (!$c->has('wp/roles')) {
                    return new NullHandler();
                }

                return new AddCapabilitiesHandler(
                    $c->get('wp/roles'),
                    $c->get('wpra/feeds/sources/menu/capability_roles'),
                    [$c->get('wpra/feeds/sources/menu/capability')]
                );
            },
            /*
             * The handler that adds the CPT's capabilities to the appropriate user roles.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/handlers/add_cpt_capabilities' => function (ContainerInterface $c) {
                return new AddCptMetaCapsHandler(
                    $c->get('wp/roles'),
                    $c->get('wpra/feeds/sources/cpt/capability_roles'),
                    $c->get('wpra/feeds/sources/cpt/capability')
                );
            },
            /*
             * The full handler for adding all capabilities related to the feed sources CPT.
             *
             * @since 4.13
             */
            'wpra/feeds/sources/add_capabilities_handler' => function (ContainerInterface $c) {
                return new MultiHandler([
                    $c->get('wpra/feeds/sources/handlers/add_menu_capabilities'),
                    $c->get('wpra/feeds/sources/handlers/add_cpt_capabilities'),
                ]);
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
        add_action('init', $c->get('wpra/feeds/sources/handlers/register_cpt'));
        add_filter('the_content', $c->get('wpra/feeds/sources/handlers/render_content'));
        add_action('admin_init', $c->get('wpra/feeds/sources/add_capabilities_handler'));
    }
}
