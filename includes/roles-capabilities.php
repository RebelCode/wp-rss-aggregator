<?php

/**
 * Contains all roles and capabilities functions
 *
 * @package WPRSSAggregator
 */

/**
 * Remove core post type capabilities (called on uninstall)
 *
 * @since 3.3
 * @return void
 */
function wprss_remove_caps()
{
    if (class_exists('WP_Roles')) {
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
    }

    if (is_object($wp_roles)) {

        /** Site Administrator Capabilities */
        $wp_roles->remove_cap('administrator', 'manage_feed_settings');
        /** Editor Capabilities */
        $wp_roles->remove_cap('editor', 'manage_feed_settings');

        /** Remove the Main Post Type Capabilities */
        $capabilities = $this->get_core_caps();

        foreach ($capabilities as $cap_group) {
            foreach ($cap_group as $cap) {
                $wp_roles->remove_cap('administrator', $cap);
                $wp_roles->remove_cap('editor', $cap);
            }
        }
    }
}
