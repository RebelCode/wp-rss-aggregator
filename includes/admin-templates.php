<?php

use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\WpCptDataSet;
use RebelCode\Wpra\Core\Templates\BuiltInFeedTemplate;
use RebelCode\Wpra\Core\Templates\WpPostFeedTemplate;

/* @since [*next-version*] */
define('WPRSS_FEED_TEMPLATE_CPT', 'wprss_feed_template');

add_action('init', function () {
    $labels = apply_filters(
        'wprss_feed_template_post_type_labels',
        [
            'name' => __('Templates', WPRSS_TEXT_DOMAIN),
            'singular_name' => __('Template', WPRSS_TEXT_DOMAIN),
            'add_new' => __('Add New', WPRSS_TEXT_DOMAIN),
            'all_items' => __('Templates', WPRSS_TEXT_DOMAIN),
            'add_new_item' => __('Add New Template', WPRSS_TEXT_DOMAIN),
            'edit_item' => __('Edit Template', WPRSS_TEXT_DOMAIN),
            'new_item' => __('New Template', WPRSS_TEXT_DOMAIN),
            'view_item' => __('View Template', WPRSS_TEXT_DOMAIN),
            'search_items' => __('Search Feeds', WPRSS_TEXT_DOMAIN),
            'not_found' => __('No Templates Found', WPRSS_TEXT_DOMAIN),
            'not_found_in_trash' => __('No Templates Found In Trash', WPRSS_TEXT_DOMAIN),
            'menu_name' => __('Templates', WPRSS_TEXT_DOMAIN),
        ]
    );

    $args = apply_filters(
        'wprss_feed_templates_post_type_args',
        [
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'public' => true,
            'show_ui' => false,
            'query_var' => 'feed_template',
            'menu_position' => 100,
            'show_in_menu' => false,
            'rewrite' => [
                'slug' => 'feed-templates',
                'with_front' => false,
            ],
            'capability_type' => 'feed_template',
            'map_meta_cap' => true,
            'supports' => ['title'],
            'labels' => $labels,
        ]
    );

    register_post_type(WPRSS_FEED_TEMPLATE_CPT, $args);
});

// Adds the "Templates" page and menu item
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=wprss_feed',
        __('Templates', 'wprss'),
        __('Templates', 'wprss'),
        'edit_feed_templates',
        'wprss-feed-templates',
        'wprss_render_admin_templates_page'
    );
}, 10);

/**
 * Renders the admin "Templates" page.
 *
 * @since [*next-version*]
 */
function wprss_render_admin_templates_page()
{
}
