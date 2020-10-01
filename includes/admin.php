<?php
    /**
     * Plugin administration related functions
     *
     * @package WPRSSAggregator
     */

    add_action( 'admin_head', 'wprss_custom_post_type_icon' );
    /**
     * Custom Post Type Icon for Admin Menu & Post Screen
     * @since  2.0
     */
    function wprss_custom_post_type_icon() {
        ?>
        <style>
            /* Post Screen - 32px */
            .icon32-posts-wprss_feed {
                background: transparent url( <?php echo WPRSS_IMG . 'icon-adminpage32.png'; ?> ) no-repeat left top !important;
            }
            /* Post Screen - 32px */
            .icon32-posts-wprss_feed_item {
                background: transparent url( <?php echo WPRSS_IMG . 'icon-adminpage32.png'; ?> ) no-repeat left top !important;
            }
        </style>
    <?php }

    add_action('admin_menu', function () {
        add_submenu_page( 'edit.php?post_type=wprss_feed', __( 'WP RSS Aggregator Settings', WPRSS_TEXT_DOMAIN ), __( 'Settings', WPRSS_TEXT_DOMAIN ), apply_filters( 'wprss_capability', 'manage_feed_settings' ), 'wprss-aggregator-settings', 'wprss_settings_page_display' );
    }, 30);

    add_action('admin_menu', function () {
        add_submenu_page( 'edit.php?post_type=wprss_feed', __( 'Help & Support', WPRSS_TEXT_DOMAIN ), __( 'Help & Support', WPRSS_TEXT_DOMAIN ), apply_filters( 'wprss_capability', 'manage_feed_settings'), 'wprss-help', 'wprss_help_page_display' );
    }, 60);

    // Hides the "Add New" submenu
    add_action('admin_menu', function () {
        global $submenu;
        unset( $submenu['edit.php?post_type=wprss_feed'][10] );
    });

    add_filter('admin_body_class', 'wprss_base_admin_body_class');
    /**
     * Set body class for admin screens
     * http://www.kevinleary.net/customizing-wordpress-admin-css-javascript/
     * @since 2.0
     */
    function wprss_base_admin_body_class( $classes )
    {
        // Current action
        if ( is_admin() && isset($_GET['action']) ) {
            $classes .= ' action-'.$_GET['action'];
        }
        // Current post ID
        if ( is_admin() && isset($_GET['post']) ) {
            $classes .= ' post-'.$_GET['post'];
        }
        // New post type & listing page
        if ( isset($_GET['post_type']) ) $post_type = $_GET['post_type'];
        if ( isset($post_type) ) {
            $classes .= ' post-type-'.$post_type;
        }
        // Editting a post type
        if ( isset( $_GET['post'] ) ) {
            $post_query = $_GET['post'];
        }
        if ( isset($post_query) ) {
            $current_post_edit = get_post($post_query);
            $current_post_type = $current_post_edit->post_type;
            if ( !empty($current_post_type) ) {
                $classes .= ' post-type-'.$current_post_type;
            }
        }
        // Return the $classes array
        return $classes;
    }


    /**
     * Change title on wprss_feed post type screen
     *
     * @since  2.0
     *
     * @param string $original The original title placeholder.
     *
     * @return string
     */
    function wprss_change_title_text($original) {
        if (get_post_type() === 'wprss_feed') {
            return __('Name this feed', WPRSS_TEXT_DOMAIN);
        }

        return $original;
    }


    add_filter( 'plugin_action_links', 'wprss_plugin_action_links', 10, 2 );
    /**
     * Add Settings action link in plugin listing
     *
     * @since  3.0
     * @param  array  $action_links
     * @param  string $plugin_file
     * @return array
     */
    function wprss_plugin_action_links( $action_links, $plugin_file ) {
        // check to make sure we are on the correct plugin
        if ( $plugin_file == 'wp-rss-aggregator/wp-rss-aggregator.php' ) {
            // the anchor tag and href to the URLs we want.
            $settings_link = '<a href="' . admin_url() . 'edit.php?post_type=wprss_feed&page=wprss-aggregator-settings">' . __( 'Settings', WPRSS_TEXT_DOMAIN ) . '</a>';
            $docs_link = '<a href="https://www.wprssaggregator.com/documentation/">' . __( 'Documentation', WPRSS_TEXT_DOMAIN ) . '</a>';
            // add the links to the beginning of the list
            array_unshift( $action_links, $settings_link, $docs_link );
        }
        return $action_links;
    }

    /**
     * Function for registering application's scripts that depends on advanced libraries.
     * It will enqueue manifest and vendor scripts, which contains all required logic
     * for bootstrapping application and dependencies.
     *
     * Use only for Vue-related apps that use Webpack for being built.
     *
     * @since 4.12.1
     *
     * @param $handle
     * @param string $src
     * @param array $deps
     * @param bool $ver
     * @param bool $in_footer
     */
    function wprss_plugin_enqueue_app_scripts( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
        /*
         * Manifest file holds function used for bootstrapping and ordered
         * loading of dependencies and application.
         */
        wp_enqueue_script('wpra-manifest', WPRSS_APP_JS . 'wpra-manifest.min.js', array(), '0.1', true);

        /*
         * Vendor file holds all common dependencies for "compilable" applications.
         *
         * For example, `intro` pages application's and plugin's page application's files
         * holds only logic for that particular application. Common dependencies like Vue
         * live in this file and loaded before that application.
         */
        wp_enqueue_script('wpra-vendor', WPRSS_APP_JS . 'wpra-vendor.min.js', array(
            'wpra-manifest'
        ), '0.1', true);

        /*
         * Enqueue requested script.
         */
        $deps = array_merge(array(
            'wpra-manifest',
            'wpra-vendor',
        ), $deps);
        wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
    }

    add_filter('admin_footer_text', 'wprss_admin_footer');
    /**
     * Adds footer text on the plugin pages.
     *
     * @param string $footer The footer text to filter
     *
     * @return string         The filtered footer text with added plugin text, or the param
     *                        value if the page is not specific to the plugin.
     */
    function wprss_admin_footer($footer)
    {
        // Current post type
        global $typenow;
        // Check if type is a plugin type. If not, stop
        // Plugin type is in the form 'wprss_*'' where * is 'feed', 'blacklist', etc)
        if (stripos($typenow, 'wprss_') !== 0) {
            return $footer;
        }

        // Prepare fragments of the message
        $thank_you = sprintf(
            __('Thank you for using <a href="%1$s" target="_blank">WP RSS Aggregator</a>.', 'wprss'),
            'https://www.wprssaggregator.com/'
        );
        $rate_us = sprintf(
            __('Please <a href="%1$s" target="_blank">rate us</a>!', 'wprss'),
            'https://wordpress.org/support/view/plugin-reviews/wp-rss-aggregator?filter=5#postform'
        );

        // Return the final text
        return sprintf('<span class="wp-rss-footer-text">%1$s %2$s</span>', $thank_you, $rate_us);
    }
