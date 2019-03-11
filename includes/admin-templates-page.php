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

    echo wprss_render_template('admin-templates-page.twig', array(
        'title' => 'Templates',
        'subtitle' => 'Follow these introductory steps to get started with WP RSS Aggregator.',
    ));
}
