<?php

define('WPRSS_UPDATE_PAGE_SLUG', 'wpra-update');
define('WPRSS_UPDATE_PAGE_PREV_VERSION_OPTION', 'wprss_prev_update_page_version');

/**
 * Registers the update page.
 *
 * @since [*next-version*]
 */
add_action('admin_menu', function () {
    add_submenu_page(
        null,
        __('Thank you for updating WP RSS Aggregator', WPRSS_TEXT_DOMAIN),
        __('Thank you for updating WP RSS Aggregator', WPRSS_TEXT_DOMAIN),
        'manage_options',
        WPRSS_UPDATE_PAGE_SLUG,
        'wprss_render_update_page'
    );
});

/**
 * Renders the update page.
 *
 * @since [*next-version*]
 *
 * @throws Twig_Error_Loader
 * @throws Twig_Error_Runtime
 * @throws Twig_Error_Syntax
 */
function wprss_render_update_page()
{
    wprss_update_previous_update_page_version();

    echo wprss_render_template('admin-update-page.twig', [
        'title' => __('Thank you for updating to the latest version of WP RSS Aggregator', WPRSS_TEXT_DOMAIN),
        'subtitle' => sprintf(_x('Version %s', '%s is the current plugin version', WPRSS_TEXT_DOMAIN), WPRSS_VERSION),
        'version' => WPRSS_VERSION
    ]);
}

/**
 * Retrieves the URL of the update page.
 *
 * @since [*next-version*]
 *
 * @return string
 */
function wprss_get_update_page_url()
{
    return menu_page_url(WPRSS_UPDATE_PAGE_SLUG, false);
}

/**
 * Checks whether the update should be shown or not, based on whether the user is new and previously had an older
 * version of the plugin.
 *
 * @since [*next-version*]
 *
 * @return bool True if the update page should be shown, false if not.
 */
function wprss_should_do_update_page()
{
    return !wprss_is_new_user() && current_user_can('manage_options') && wprss_user_had_previous_version();
}

/**
 * Checks whether the user had a previous version of WP RSS Aggregator.
 *
 * @since [*next-version*]
 *
 * @return mixed
 */
function wprss_user_had_previous_version()
{
    $previous = wprss_get_previous_update_page_version();
    $is_newer = version_compare(WPRSS_VERSION, $previous, '>');

    return $is_newer;
}

/**
 * Updates the previous update page version to the current plugin version.
 *
 * @since [*next-version*]
 */
function wprss_update_previous_update_page_version()
{
    update_option(WPRSS_UPDATE_PAGE_PREV_VERSION_OPTION, WPRSS_VERSION);
}

/**
 * Retrieves the previous update page version seen by the user, if at all.
 *
 * @since [*next-version*]
 *
 * @return string The version string, or '0.0.0' if the user is new nad has not used WPRA before.
 */
function wprss_get_previous_update_page_version()
{
    wprss_migrate_welcome_page_to_update_page();

    return get_option(WPRSS_UPDATE_PAGE_PREV_VERSION_OPTION, '0.0.0');
}

/**
 * Migrates the previously used "welcome screen" version DB option.
 *
 * @since [*next-version*]
 */
function wprss_migrate_welcome_page_to_update_page()
{
    // Get the previous welcome screen version - for when the page was called the "welcome screen"
    $pwsv = get_option('wprss_pwsv', null);

    // If the option exists, move it to the new option
    if ($pwsv !== null) {
        update_option(WPRSS_UPDATE_PAGE_PREV_VERSION_OPTION, $pwsv);
        delete_option('wprss_pwsv');
    }
}
