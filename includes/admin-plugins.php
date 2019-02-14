<?php

if (!defined('ABSPATH')) {
    die;
}

/**
 * Detects an activation and redirects the user to the intro page.
 *
 * @since [*next-version*]
 */
add_action('admin_init', function () {
    $page = trim($_SERVER["REQUEST_URI"] , '/');
    $isPluginsPage = strpos($page, 'plugins.php') !== false;

    if (!$isPluginsPage) {
        return;
    }

    add_action('admin_footer', function () {
        wprss_plugin_enqueue_app_scripts('wpra-plugins', WPRSS_JS . 'plugins.min.js', [], '0.1', true);
        wp_enqueue_style('wpra-plugins', WPRSS_CSS . 'plugins.min.css');
        echo '<div id="wpra-plugins-app"></div>';
    });
});
