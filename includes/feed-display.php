<?php

use RebelCode\Wpra\Core\Templates\ListTemplateType;
use RebelCode\Wpra\Core\Templates\MasterFeedsTemplate;

/**
 * Feed display related functions
 *
 * @package WPRSSAggregator
 */

if (defined('WPRSS_USE_LEGACY_FEED_DISPLAY') && WPRSS_USE_LEGACY_FEED_DISPLAY) {
    require_once(WPRSS_INC . 'leagacy-feed-display.php');
    die;
}

// Hooks in the handler for server-side feed item rendering
add_action('wp_ajax_wprss_render', 'wp_render_ajax');
add_action('wp_ajax_nopriv_wprss_render', 'wp_render_ajax');

/**
 * The handler for server-side feed item rendering.
 *
 * @since [*next-version*]
 */
function wp_render_ajax() {
    $args = filter_input(INPUT_GET, 'wprss_render_args', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY | FILTER_NULL_ON_FAILURE);
    $args = is_array($args) ? $args : [];

    echo json_encode(['render' => wprss_render_feeds($args), 'page' => $args['page']]);
    die;
}

/**
 * Retrieves the WP RSS Aggregator master template for rendering feed items.
 *
 * @since [*next-version*]
 *
 * @return MasterFeedsTemplate
 */
function wprss_get_master_feeds_template()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new MasterFeedsTemplate('default');
        // Register the core list template
        $instance->addTemplateType(new ListTemplateType());
        // Trigger action to allow registration of additional template types
        do_action('wprss_register_template_types', $instance);
    }

    return $instance;
}

/**
 * Renders WP RSS Aggregator imported feed items.
 *
 * @since [*next-version*]
 *
 * @param array $ctx Optional template render context.
 *
 * @return string
 */
function wprss_render_feeds($ctx = [])
{
    $ctx = is_array($ctx) ? $ctx : [];

    return wprss_get_master_feeds_template()->render($ctx);
}

/**
 * Outputs rendered feed items on the front end.
 *
 * @since 2.0
 *
 * @param array $args Optional arguments for which items to render and how to render them.
 */
function wprss_display_feed_items($args = [])
{
    echo wprss_render_feeds($args);
}

/**
 * Redirects to wprss_display_feed_items
 * It is used for backwards compatibility to versions < 2.0
 *
 * @since 2.1
 *
 * @param array $args Optional arguments for which items to render and how to render them.
 */
function wp_rss_aggregator($args = [])
{
    wprss_display_feed_items($args);
}

/**
 * Retrieve settings and prepare them for use in the display function
 *
 * @since 3.0
 *
 * @param array $settings The settings.
 *
 * @return array
 */
function wprss_get_display_settings($settings = null)
{
    if ($settings === null) {
        $settings = get_option('wprss_settings_general');
    }
    // Parse the arguments together with their default values
    $args = wp_parse_args(
        $settings,
        [
            'open_dd' => 'blank',
            'follow_dd' => '',
        ]
    );

    // Prepare the 'open' setting - how to open links for feed items
    $open = '';
    switch ($args['open_dd']) {
        case 'lightbox' :
            $open = 'class="colorbox"';
            break;
        case 'blank' :
            $open = 'target="_blank"';
            break;
    }

    // Prepare the 'follow' setting - whether links marked as nofollow or not
    $follow = ($args['follow_dd'] == 'no_follow') ? 'rel="nofollow"' : '';

    // Prepare the final settings array
    $display_settings = [
        'open' => $open,
        'follow' => $follow,
    ];

    do_action('wprss_get_settings');

    return $display_settings;
}

/**
 * Generates an HTML link, using the saved display settings.
 *
 * @param string $link The link URL
 * @param string $text The link text to display
 * @param bool $bool Optional boolean. If FALSE, the text is returned unlinked. Default: TRUE.
 *
 * @return string The generated link
 * @since 4.2.4
 */
function wprss_link_display($link, $text, $bool = true)
{
    $display_settings = wprss_get_display_settings(get_option('wprss_settings_general'));
    $a = $bool ? "<a {$display_settings['open']} {$display_settings['follow']} href='$link'>$text</a>" : $text;

    return $a;
}

/**
 * Limits a phrase/content to a defined number of words
 *
 * NOT BEING USED as we're using the native WP function, although the native one strips tags, so I'll
 * probably revisit this one again soon.
 *
 * @since  3.0
 *
 * @param  string  $words
 * @param  integer $limit
 * @param  string  $append
 *
 * @return string
 */
function wprss_limit_words($words, $limit, $append = '')
{
    /* Add 1 to the specified limit becuase arrays start at 0 */
    $limit = $limit + 1;
    /* Store each individual word as an array element
       up to the limit */
    $words = explode(' ', $words, $limit);
    /* Shorten the array by 1 because that final element will be the sum of all the words after the limit */
    array_pop($words);
    /* Implode the array for output, and append an ellipse */
    $words = implode(' ', $words) . $append;

    /* Return the result */

    return rtrim($words);
}
