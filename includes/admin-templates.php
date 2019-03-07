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

    register_post_type(WPRSS_FEED_TEMPLATE_CPT, $args);
});

// Temporary meta box for development purposes
add_action('add_meta_boxes', function () {
    add_meta_box(
        'wpra-template-data',
        'Template Data',
        function () {
            global $post;
            $template = wprss_create_template_from_post($post);
            printf('<p><b>Template type:</b> %s</p>', $template['template_type']);
            echo '<pre>';
            print_r(iterator_to_array($template));
            echo '</pre>';
        },
        'wprss_feed_template'
    );
});

/**
 * Retrieves WP RSS Aggregator user templates.
 *
 * @since [*next-version*]
 *
 * @param string|array|null $statuses The status to retrieve, an array of statuses to retrieve or null to retrieve all.
 *
 * @return DataSetInterface[] The templates as data set model instances.
 */
function wprss_get_templates($statuses = 'publish')
{
    $statuses = ($statuses === null) ? ['publish', 'draft', 'trash'] : $statuses;
    $statuses = (is_string($statuses)) ? [$statuses] : $statuses;

    $posts = get_posts([
        'post_type' => WPRSS_FEED_TEMPLATE_CPT,
        'post_status' => $statuses,
        'posts_per_page' => -1,
    ]);

    return array_map(function ($post) {
        return wprss_create_template_from_post($post);
    }, $posts);
}

/**
 * Creates a WP RSS Aggregator template data set model instance from a WordPress post.
 *
 * @since [*next-version*]
 *
 * @param WP_Post $post The post instance.
 *
 * @return WpCptDataSet The data set model instance.
 */
function wprss_create_template_from_post(WP_Post $post)
{
    $templateType = get_post_meta($post->ID, 'wprss_template_type', true);

    return ($templateType === '__built_in')
        ? new BuiltInFeedTemplate($post)
        : new WpPostFeedTemplate($post);
}

// This ensures that there is always at least one template available, by constructing the core list template
// from the old general display settings.
add_action('init', function () {
    $templates = wprss_get_templates();

    if (count($templates) === 0) {
        wp_insert_post([
            'post_type' => WPRSS_FEED_TEMPLATE_CPT,
            'post_title' => __('Default'),
            'post_name' => 'default',
            'post_status' => 'publish',
            'meta_input' => [
                'wprss_template_type' => '__built_in',
            ],
        ]);
    }
});
