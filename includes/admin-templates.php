<?php

use RebelCode\Wpra\Core\Templates\WpPostFeedTemplate;

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
            'show_ui' => true,
            'query_var' => 'feed_template',
            'menu_position' => 100,
            'show_in_menu' => 'edit.php?post_type=wprss_feed',
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

    register_post_type('wprss_feed_template', $args);
});

// Temporary meta box for development purposes
add_action('add_meta_boxes', function () {
    add_meta_box(
        'wpra-template-data',
        'Template Data',
        function () {
            global $post;
            $template = new WpPostFeedTemplate($post);
            printf('<p><b>Template type:</b> %s</p>', $template['template_type']);
            echo '<pre>';
            print_r(iterator_to_array($template));
            echo '</pre>';
        },
        'wprss_feed_template'
    );
});

// This ensures that there is always at least one template available, by constructing the core list template
// from the old general display settings.
add_action('init', function () {
    $counts = (array) wp_count_posts('wprss_feed_template');
    if (array_sum($counts) === 0) {
        wp_insert_post([
            'post_type' => 'wprss_feed_template',
            'post_title' => __('Default'),
            'post_name' => 'default',
            'post_status' => 'publish',
            'meta_input' => [
                'wprss_template_type' => '__built_in',
            ]
        ]);
    }
});
