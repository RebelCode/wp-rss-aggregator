<?php

/**
 * Set up shortcodes and call the main function for output
 *
 * @since 1.0
 */
function wprss_shortcode($atts = [])
{

    //Enqueue scripts / styles
    wp_enqueue_script('jquery.colorbox-min', WPRSS_JS . 'jquery.colorbox-min.js', ['jquery']);
    wp_enqueue_script('wprss_custom', WPRSS_JS . 'custom.js', ['jquery', 'jquery.colorbox-min']);

    $general_settings = get_option('wprss_settings_general');

    if (!$general_settings['styles_disable'] == 1) {
        wp_enqueue_style('colorbox', WPRSS_CSS . 'colorbox.css', [], '1.4.33');
        wp_enqueue_style('styles', WPRSS_CSS . 'styles.css', [], '');
    }

    if (!empty ($atts)) {
        foreach ($atts as $key => &$val) {
            $val = html_entity_decode($val);
        }
    }

    return apply_filters('wprss_shortcode_output', wprss_render($atts));
}

// Register shortcodes
add_shortcode('wp_rss_aggregator', 'wprss_shortcode');
add_shortcode('wp-rss-aggregator', 'wprss_shortcode');
