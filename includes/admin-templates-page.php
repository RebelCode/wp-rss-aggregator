<?php

if (!defined('ABSPATH')) {
    die;
}

/**
 * Registers the introduction page.
 *
 * @since 4.12
 */
add_action('admin_menu', function () {
    add_submenu_page(
        null,
        __('Templates'),
        __('Templates'),
        'manage_options',
        'wpra-templates',
        'wprss_render_templates_page'
    );
});

/**
 * Renders the intro page.
 *
 * @since 4.12
 *
 * @throws Twig_Error_Loader
 * @throws Twig_Error_Runtime
 * @throws Twig_Error_Syntax
 */
function wprss_render_templates_page()
{
    wprss_plugin_enqueue_app_scripts('wpra-templates', WPRSS_JS . 'templates.min.js', array(), '0.1', true);
    wp_enqueue_style('wpra-templates', WPRSS_CSS . 'templates.min.css');

    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

    wp_localize_script('wpra-templates', 'WpraGlobal', [
        'templates_url_base' => str_replace($url, '', menu_page_url('wpra-templates', false)),
        'nonce' => wp_create_nonce('wp_rest'),
    ]);

    wp_localize_script('wpra-templates', 'WpraTemplates', [
        'model_schema' => apply_filters('wpra-template-model-schema', [
            'id' => '',
            'name' => '',
            'slug' => '',
            'type' => 'list',
            'options' => [
                'items_max_num' => 15,
                'title_max_length' => 0,
                'title_is_link' => true,
                'pagination' => true,
                'pagination_type' => 'default',
                'source_enabled' => true,
                'source_prefix' => __('Source:', 'wprss'),
                'source_is_link' => true,
                'author_enabled' => false,
                'author_prefix' =>  __('By', 'wprss'),
                'date_enabled' => true,
                'date_prefix' => __('Published on:', 'wprss'),
                'date_format' => 'Y-m-d',
                'date_use_time_ago' => false,
                'links_behavior' => 'blank',
                'links_nofollow' => false,
                'links_video_embed_page' => false,
                'bullets_enabled' => true,
                'bullet_type' => 'default',
                'custom_css_classname' => '',
            ]
        ]),
        'options' => [
            'type' => [
                '__built_in' => 'List',
                'list' => 'List',
                'grid' => 'Grid',
            ],
            'links_behavior' => [
                'self' => 'Self',
                'blank' => 'Open in a new tab',
                'lightbox' => 'Open in a lightbox'
            ],
            'pagination_type' => [
                'default' => 'Default',
                'numbered' => 'Numbered',
            ],
            'bullet_type' => [
                'default' => 'Bullets',
                'numbers' => 'Numbers',
            ]
        ],
        'base_url' => rest_url('/wpra/v1/templates'),
    ]);

    echo wprss_render_template('admin-templates-page.twig', array(
        'title' => 'Templates',
        'subtitle' => 'Follow these introductory steps to get started with WP RSS Aggregator.',
    ));
}
