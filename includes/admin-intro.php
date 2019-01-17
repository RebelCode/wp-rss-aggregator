<?php

if (!defined('ABSPATH')) {
    die;
}

define('WPRSS_INTRO_PAGE_SLUG', 'wpra-intro');
define('WPRSS_FIRST_ACTIVATION_OPTION', 'wprss_first_activation_time');
define('WPRSS_PREV_WELCOME_SCREEN_OPTION', 'wprss_pwsv');
define('WPRSS_INTRO_DONE_OPTION', 'wprss_did_intro');
define('WPRSS_INTRO_FEED_ID_OPTION', 'wprss_intro_feed_id');
define('WPRSS_INTRO_FEED_LIMIT', 20);
define('WPRSS_INTRO_STEP_OPTION', 'wprss_intro_step');
define('WPRSS_INTRO_NONCE_NAME', 'wprss_intro_nonce');
define('WPRSS_INTRO_STEP_POST_PARAM', 'wprss_intro_step');
define('WPRSS_INTRO_FEED_URL_PARAM', 'wprss_intro_feed_url');
define('WPRSS_INTRO_SHORTCODE_PAGE_OPTION', 'wprss_intro_shortcode_page');
define('WPRSS_INTRO_SHORTCODE_PAGE_PREVIEW_PARAM', 'wprss_preview_shortcode_page');

/**
 * Registers the introduction page.
 *
 * @since [*next-version*]
 */
add_action('admin_menu', function () {
    add_submenu_page(
        null,
        __('Welcome to WP RSS Aggregator'),
        __('Welcome to WP RSS Aggregator'),
        'read',
        WPRSS_INTRO_PAGE_SLUG,
        'wpra_render_intro_page'
    );
});

/**
 * Renders the intro page.
 *
 * @since [*next-version*]
 *
 * @throws Twig_Error_Loader
 * @throws Twig_Error_Runtime
 * @throws Twig_Error_Syntax
 */
function wpra_render_intro_page()
{
    wp_enqueue_script('intro-wizard', WPRSS_JS . 'intro.min.js', [], '0.1', true);
    wp_enqueue_style('intro-wizard', WPRSS_CSS . 'intro.min.css');

    $nonce = wp_create_nonce(WPRSS_INTRO_NONCE_NAME);
    wp_localize_script('intro-wizard', 'wprssWizardConfig', [
        'previewUrl' => admin_url('admin.php?wprss_preview_shortcode_page=1&nonce=' . $nonce),
        'feedListUrl' => admin_url('edit.php?post_type=wprss_feed'),
        'addOnsUrl' => 'https://www.wprssaggregator.com/plugins/',
        'supportUrl' => 'https://www.wprssaggregator.com/contact/',
        'feedEndpoint' => [
            'url' => admin_url('admin-ajax.php'),
            'defaultPayload' => [
                'action' => 'wprss_create_intro_feed',
                'nonce' => $nonce,
            ],
        ],
    ]);

    echo wprss_render_template('admin-intro-page.twig', [
        'title' => 'Welcome to WP RSS Aggregator 👋',
        'subtitle' => 'Follow these introductory steps to get started with WP RSS Aggregator.',
    ]);
}

/**
 * AJAX handler for setting the introduction step the user has reached.
 *
 * @since [*next-version*]
 */
add_action('wp_ajax_wprss_set_intro_step', function () {
    check_ajax_referer(WPRSS_INTRO_NONCE_NAME, 'nonce');
    if (!current_user_can('manage_options')) {
        wp_die('', '', [
            'response' => 403,
        ]);
    }

    $step = filter_input(INPUT_POST, WPRSS_INTRO_STEP_POST_PARAM, FILTER_VALIDATE_INT);

    if ($step === null) {
        wprss_ajax_error_response(
            sprintf(__('Missing intro step param "%s"', WPRSS_TEXT_DOMAIN), WPRSS_INTRO_STEP_POST_PARAM)
        );
    }

    wprss_set_intro_step($step);
    wprss_ajax_success_response([
        'wprss_intro_step' => $step,
    ]);
});

/**
 * AJAX handler for creating a feed source from the introduction and previewing its items.
 *
 * @since [*next-version*]
 */
add_action('wp_ajax_wprss_create_intro_feed', function () {
    check_ajax_referer(WPRSS_INTRO_NONCE_NAME, 'nonce');
    if (!current_user_can('manage_options')) {
        wp_die('', '', [
            'response' => 403,
        ]);
    }

    $url = filter_input(INPUT_POST, WPRSS_INTRO_FEED_URL_PARAM, FILTER_VALIDATE_URL);

    if ($url === null) {
        wprss_ajax_error_response(
            __('Missing feed URL parameter', WPRSS_TEXT_DOMAIN)
        );
    }
    if ($url === false) {
        wprss_ajax_error_response(
            __('The given feed URL is invalid', WPRSS_TEXT_DOMAIN)
        );
    }

    try {
        wprss_create_intro_feed_source($url);
        $items = wprss_preview_feed_items($url);
        $data = [
            'feed_items' => $items,
        ];
        wprss_ajax_success_response($data);
    } catch (Exception $e) {
        wprss_ajax_error_response($e->getMessage(), 500);
    }
});

/**
 * Handler that redirects to the intro-created page that contains the WP RSS Aggregator shortcode.
 *
 * The page is created automatically if it doesn't exist.
 *
 * @since [*next-version*]
 */
add_action('init', function () {
    if (!filter_input(INPUT_GET, WPRSS_INTRO_SHORTCODE_PAGE_PREVIEW_PARAM, FILTER_VALIDATE_BOOLEAN)) {
        return;
    }

    check_admin_referer(WPRSS_INTRO_NONCE_NAME, 'nonce');
    if (!current_user_can('manage_options')) {
        wp_die('', '', [
            'response' => 403,
        ]);
    }

    try {
        $id = wprss_get_intro_shortcode_page();
        $url = get_preview_post_link($id);

        if ($url === null) {
            throw new Exception('Failed to get the preview URL for the page');
        }

        wp_redirect($url);
    } catch (Exception $e) {
        wp_die($e->getMessage());
    }
});

/**
 * Previews a feed by fetching some feed items.
 *
 * @since [*next-version*]
 *
 * @param string $url The URL of the feed source.
 * @param int    $max The maximum number of items to fetch.
 *
 * @return array An array of feed items, as associative arrays containing the following keys:
 *               * title - The feed title
 *               * permalink - The URL of the original article
 *               * date - The published date of the original article
 *               * author - The name of the author
 *
 * @throws Exception If failed to fetch the feed items.
 */
function wprss_preview_feed_items($url, $max = 10)
{
    $items = wprss_get_feed_items($url, null);

    if ($items === null) {
        throw new Exception(__('Failed to retrieve items'));
    }

    $count = 0;
    $results = [];
    foreach ($items as $item) {
        /* @var $item SimplePie_Item */
        $results[] = [
            'title' => $item->get_title(),
            'permalink' => $item->get_permalink(),
            'date' => $item->get_date(get_option('date_format')),
            'author' => $item->get_author()->name,
        ];

        if ($count++ > $max) {
            break;
        }
    }

    return $results;
}

/**
 * Creates the feed source for the onboarding introduction process.
 *
 * @since [*next-version*]
 *
 * @param string $url The URL.
 *
 * @return int The ID of the feed source.
 *
 * @throws Exception If an error occurred while creating the feed source.
 */
function wprss_create_intro_feed_source($url)
{
    $feedId = get_option(WPRSS_INTRO_FEED_ID_OPTION, 0);
    $feed = get_post($feedId, OBJECT);

    if ($feed === null || $feed->post_status != 'publish') {
        $newId = wprss_create_feed_source_with_url($url);
        update_post_meta($newId, 'wprss_limit', WPRSS_INTRO_FEED_LIMIT);
        update_option(WPRSS_INTRO_FEED_ID_OPTION, $newId);

        return $newId;
    }

    // Update the existing feed source with a new generated name and new URL
    wp_update_post([
        'ID' => $feedId,
        'post_title' => wprss_feed_source_name_from_url($url),
        'post_status' => 'publish',
        'meta_input' => [
            'wprss_url' => $url,
            'wprss_limit' => WPRSS_INTRO_FEED_LIMIT
        ]
    ]);

    // Re-import the items for this feed
    wprss_delete_feed_items($feedId);
    wprss_fetch_insert_single_feed_items($feedId);

    return $feedId;
}

/**
 * Creates a feed source with a given URL.
 *
 * @since [*next-version*]
 *
 * @param string $url The URL of the RSS feed.
 *
 * @return int The ID of the created feed source.
 *
 * @throws Exception If an error occurred while creating the feed source.
 */
function wprss_create_feed_source_with_url($url)
{
    $name = wprss_feed_source_name_from_url($url);
    $result = wprss_import_feed_sources_array([$url => $name]);

    if (empty($result)) {
        throw new Exception(
            sprintf(__('Failed to import the feed source "%s" with URL "%s"', WPRSS_TEXT_DOMAIN), $name, $url)
        );
    }

    if ($result[0] instanceof Exception) {
        throw $result[0];
    }

    return $result[0];
}

/**
 * Imports feed sources from an associative array.
 *
 * @since [*next-version*]
 *
 * @param string[] $array An array of feed source URLs mapping to feed source names.
 *
 * @return array The import results. For each source representation (in order), the result will be one of:
 *               - Integer, representing the ID of the created resource;
 *               - An {@link Exception} if something went wrong during import.
 */
function wprss_import_feed_sources_array($array)
{
    /* @var $importer Aventura\Wprss\Core\Component\BulkSourceImport */
    $importer = wprss_wp_container()->get(\WPRSS_SERVICE_ID_PREFIX . 'array_source_importer');

    return $importer->import($array);
}

/**
 * Generates a feed source name from a feed source URL.
 *
 * @since [*next-version*]
 *
 * @param string $url The URL.
 *
 * @return string The generated name.
 */
function wprss_feed_source_name_from_url($url)
{
    $name = parse_url($url, PHP_URL_HOST);
    $name = ($name === null) ? $url : $name;

    return $name;
}

/**
 * Retrieves the ID of the page with the shortcode for the introduction, creating it if necessary.
 *
 * @since [*next-version*]
 *
 * @return int The ID of the page.
 *
 * @throws Exception If failed to create the page.
 */
function wprss_get_intro_shortcode_page()
{
    $id = get_option(WPRSS_INTRO_SHORTCODE_PAGE_OPTION, 0);
    $page = get_post($id);

    if (!$page) {
        $id = wprss_create_shortcode_page();
        update_option(WPRSS_INTRO_SHORTCODE_PAGE_OPTION, $id);
    }

    return $id;
}

/**
 * Creates a page that contains the WP RSS Aggregator shortcode.
 *
 * @since [*next-version*]
 *
 * @param string|null $title  Optional title for the page.
 * @param string      $status Optional status of the page.
 *
 * @return int The ID of the created page.
 *
 * @throws Exception If failed to create the page.
 */
function wprss_create_shortcode_page($title = null, $status = 'draft')
{
    $title = ($title === null)
        ? _x('Feeds', 'default name of shortcode page', WPRSS_TEXT_DOMAIN)
        : $title;

    $id = wp_insert_post([
        'post_type' => 'page',
        'post_title' => $title,
        'post_content' => '[wp-rss-aggregator]',
        'post_status' => $status
    ]);

    if (is_wp_error($id)) {
        throw new Exception($id->get_error_message(), $id->get_error_code());
    }

    return $id;
}

/**
 * Retrieves the step the user has reached in the introduction.
 *
 * @since [*next-version*]
 *
 * @return int
 */
function wprss_get_intro_step()
{
    return get_option(WPRSS_INTRO_STEP_OPTION, 0);
}

/**
 * Sets the step the user has reached in the introduction.
 *
 * @since [*next-version*]
 *
 * @param int $step A positive integer.
 */
function wprss_set_intro_step($step)
{
    update_option(WPRSS_INTRO_STEP_OPTION, max($step, 0));
}

/**
 * Retrieves the URL of the intro page.
 *
 * @since [*next-version*]
 *
 * @return string
 */
function wprss_get_intro_page_url()
{
    return menu_page_url(WPRSS_INTRO_PAGE_SLUG, false);
}

/**
 * Checks whether the introduction should be shown or not, based on whether the user is new and has not already
 * previously done the introduction.
 *
 * @since [*next-version*]
 *
 * @return bool True if the introduction should be shown, falses if not.
 */
function wprss_should_do_intro()
{
    return wprss_is_new_user() && intval(get_option(WPRSS_INTRO_DONE_OPTION, 0)) !== 1;
}

/**
 * Checks if the user is new to WP RSS Aggregator.
 *
 * @since [*next-version*]
 *
 * @return bool True if the user is new, false if not.
 */
function wprss_is_new_user()
{
    return get_option(WPRSS_FIRST_ACTIVATION_OPTION, null) === null &&
           get_option(WPRSS_PREV_WELCOME_SCREEN_OPTION, null) === null;
}

/**
 * Sends an AJAX success response.
 *
 * @since [*next-version*]
 *
 * @param array $data   Optional data to send.
 * @param int   $status Optional HTTP status code of the response.
 */
function wprss_ajax_success_response($data = [], $status = 200)
{
    echo json_encode([
        'success' => true,
        'error' => '',
        'data' => $data,
        'status' => $status,
    ]);
    wp_die();
}

/**
 * Sends an AJAX success response.
 *
 * @since [*next-version*]
 *
 * @param string $message Optional error message.
 * @param int    $status  Optional HTTP status code of the response.
 */
function wprss_ajax_error_response($message, $status = 400)
{
    echo json_encode([
        'success' => false,
        'error' => $message,
        'data' => [],
        'status' => $status,
    ]);
    wp_die();
}
