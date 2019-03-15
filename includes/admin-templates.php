<?php

/* @since [*next-version*] */
define('WPRSS_FEED_TEMPLATE_CPT', 'wprss_feed_template');


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
