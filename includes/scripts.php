<?php
    /**
     * Scripts
     *
     * @package WPRSSAggregator
     */

    use Aventura\Wprss\Core\Licensing\License\Status as License_Status;

    add_action( 'init', 'wprss_register_scripts', 9 );
    function wprss_register_scripts()
    {
        $version = wprss()->getVersion();

        // Add the Class library, the Xdn library, and the Aventura namespace and classes
        wp_register_script( 'wprss-xdn-class', wprss_get_script_url( 'class' ), array('jquery'), $version );
        wp_register_script( 'wprss-xdn-lib', wprss_get_script_url( 'xdn' ), array('wprss-xdn-class'), $version );
        wp_register_script( 'aventura', wprss_get_script_url( 'aventura' ), array('wprss-xdn-lib'), $version );

        wp_register_script( 'wprss-admin-addon-ajax', WPRSS_JS .'admin-addon-ajax.js', array('jquery'), $version );
        wp_localize_script( 'wprss-admin-addon-ajax', 'wprss_admin_addon_ajax', array(
            'please_wait'   =>  __( 'Please wait ...', WPRSS_TEXT_DOMAIN )
        ));

        // Prepare the URL for removing bulk from blacklist, with a nonce
        $blacklist_remove_url = admin_url( 'edit.php?wprss-bulk=1' );
        $blacklist_remove_url = wp_nonce_url( $blacklist_remove_url, 'blacklist-remove-selected', 'wprss_blacklist_trash' );
        $blacklist_remove_url .= '&wprss-blacklist-remove=';
        wp_register_script( 'wprss-admin-custom', WPRSS_JS .'admin-custom.js', array('jquery','jquery-ui-datepicker','jquery-ui-slider'), $version );
        wp_localize_script( 'wprss-admin-custom', 'wprss_admin_custom', array(
            'failed_to_import'      =>  __( 'Failed to import', WPRSS_TEXT_DOMAIN ),
            'items_are_importing'   =>  __( 'Importing!', WPRSS_TEXT_DOMAIN ),
            'items_are_deleting'    =>  __( 'Deleting!', WPRSS_TEXT_DOMAIN ),
            'please_wait'           =>  __( 'Please wait ...', WPRSS_TEXT_DOMAIN ),
            'bulk_add'              =>  __( 'Bulk Add', WPRSS_TEXT_DOMAIN ),
            'ok'                    =>  __( 'OK', WPRSS_TEXT_DOMAIN ),
            'cancel'                =>  __( 'Cancel', WPRSS_TEXT_DOMAIN ),
            'blacklist_desc'        =>  __( 'The feed items listed here will be disregarded when importing new items from your feed sources.', WPRSS_TEXT_DOMAIN ),
            'blacklist_remove'      =>  __( 'Remove selected from Blacklist', WPRSS_TEXT_DOMAIN ),
            'blacklist_remove_url'  =>  $blacklist_remove_url
        ));
        // Creates the wprss_urls object in JS
        wp_localize_script( 'wprss-admin-custom', 'wprss_urls', array(
            'import_export' => admin_url('edit.php?post_type=wprss_feed&page=wprss-import-export-settings')
        ));

        wp_register_script( 'jquery-ui-timepicker-addon', WPRSS_JS .'jquery-ui-timepicker-addon.js', array('jquery','jquery-ui-datepicker'), $version );
        wp_register_script( 'wprss-custom-bulk-actions', WPRSS_JS . 'admin-custom-bulk-actions.js', array( 'jquery' ), $version );
        wp_localize_script( 'wprss-custom-bulk-actions', 'wprss_admin_bulk', array(
            'activate'  =>  __( 'Activate', WPRSS_TEXT_DOMAIN ),
            'pause'     =>  __( 'Pause', WPRSS_TEXT_DOMAIN )
        ));

        wp_register_script( 'wprss-feed-source-table-heartbeat', WPRSS_JS .'heartbeat.js', array(), $version );
        wp_localize_script( 'wprss-feed-source-table-heartbeat', 'wprss_admin_heartbeat', array(
            'ago'   =>  __( 'ago', WPRSS_TEXT_DOMAIN )
        ));
        wp_register_script( 'wprss-admin-license-manager', WPRSS_JS . 'admin-license-manager.js', array(), $version );

        wp_register_script( 'wprss-admin-licensing', WPRSS_JS . 'admin-licensing.js', array(), $version );
        wp_localize_script( 'wprss-admin-licensing', 'wprss_admin_licensing', array(
            'activating'    => __('Activating...', WPRSS_TEXT_DOMAIN),
            'deactivating'  => __('Deactivating...', WPRSS_TEXT_DOMAIN)
        ));

        wp_register_script( 'wprss-admin-help', WPRSS_JS . 'admin-help.js', array(), $version );
        wp_localize_script( 'wprss-admin-help', 'wprss_admin_help', array(
            'sending'       => __('Sending...', WPRSS_TEXT_DOMAIN),
            'sent-error'    => sprintf(__('There was an error sending the form. Please use the <a href="%s">contact form on our site.</a>', WPRSS_TEXT_DOMAIN), esc_attr('https://www.wprssaggregator.com/contact/')),
            'sent-ok'       => __("Your message has been sent and we'll send you a confirmation e-mail when we receive it.", WPRSS_TEXT_DOMAIN)
        ));

        wp_register_script( 'wprss-hs-beacon-js', WPRSS_JS . 'beacon.min.js', array(), $version );
        wp_localize_script( 'wprss-hs-beacon-js', 'WprssHelpBeaconConfig', array (
            'premiumSupport' => ( wprss_licensing_get_manager()->licenseWithStatusExists( License_Status::VALID ) )
        ));

        wp_register_script( 'wprss-gallery-js', WPRSS_JS . 'gallery.js', array('jquery'), $version, true );

        wp_register_script('wpra-tools', WPRSS_JS . 'admin/tools/main.js', ['jquery'], $version, true);
        wp_register_script('wpra-logs-tool', WPRSS_JS . 'admin/tools/logs.js', ['jquery'], $version, true);
        wp_register_script('wpra-blacklist-tool', WPRSS_JS . 'admin/tools/blacklist.js', ['jquery'], $version, true);

        $wpSchedules = wp_get_schedules();
        $globSchedule = wprss_get_general_setting('cron_interval');
        $customSchedule = [
            'display' => __('Use Global Cron', 'wprss'),
            'interval' => $wpSchedules[$globSchedule]['interval'],
        ];
        $schedules = array_merge(['global' => $customSchedule], $wpSchedules);

        wp_register_script('wpra-crons-tool', WPRSS_JS . 'admin/tools/crons.js', ['jquery'], $version, true);
        wp_localize_script('wpra-crons-tool', 'WpraCronsTool', [
            'restUrl' => rest_url(),
            'restApiNonce' => wp_create_nonce('wp_rest'),
            'globalInterval' => $globSchedule,
            'globalTime' => wprss_get_global_update_time(),
            'globalWord' => __('Global', 'wprss'),
            'perPage' => 30,
            'schedules' => $schedules
        ]);

        wp_register_script('wpra-reset-tool', WPRSS_JS . 'admin/tools/reset.js', ['jquery'], $version, true);
        wp_localize_script('wpra-reset-tool', 'WpraResetTool', [
            'message' => __('Are you sure you want to do this? This operation cannot be undone.', 'wprss')
        ]);
    }


    add_action( 'admin_enqueue_scripts', 'wprss_admin_scripts_styles' );
    /**
     * Insert required scripts, styles and filters on the admin side
     *
     * @since 2.0
     */
    function wprss_admin_scripts_styles()
    {
        $isWpraScreen = wprss_is_wprss_page();
        $screen = get_current_screen();
        $pageBase = $screen->base;
        $postType = $screen->post_type;
        $page = isset( $_GET['page'] )? $_GET['page'] : '';
        $version = wprss()->getVersion();

        // On all admin screens
        wp_enqueue_style( 'wprss-admin-editor-styles' );
        wp_enqueue_style( 'wprss-admin-tracking-styles' );
        wp_enqueue_style( 'wprss-admin-general-styles' );

        // Only on WPRA-related admin screens
        if ($isWpraScreen) {
            wprss_admin_exclusive_scripts_styles();
        }

        do_action( 'wprss_admin_scripts_styles' );
    } // end wprss_admin_scripts_styles

    /**
     * Enqueues backend scripts on WPRA-related pages only
     *
     * @since 4.10
     */
    function wprss_admin_exclusive_scripts_styles()
    {
        $screen = get_current_screen();
        $pageBase = $screen->base;
        $postType = $screen->post_type;
        $page = isset( $_GET['page'] )? $_GET['page'] : '';
        $version = wprss()->getVersion();

        wp_enqueue_style( 'wprss-admin-styles' );
        wp_enqueue_style( 'wprss-fa' );
        wp_enqueue_style( 'wprss-admin-3.8-styles' );

        wp_enqueue_script( 'wprss-xdn-class' );
        wp_enqueue_script( 'wprss-xdn-lib' );
        wp_enqueue_script( 'aventura' );

        wp_enqueue_script( 'wprss-admin-addon-ajax' );

        wp_enqueue_script( 'wprss-admin-custom' );

        wp_enqueue_script( 'jquery-ui-timepicker-addon' );
        wp_enqueue_style( 'jquery-style' );

        if ($pageBase === 'post' && $postType = 'wprss_feed') {
            // Change text on post screen from 'Enter title here' to 'Enter feed name here'
            add_filter( 'enter_title_here', 'wprss_change_title_text' );
            wp_enqueue_media();
            wp_enqueue_script( 'wprss-gallery-js' );
        }
        if ('wprss_feed' === $postType) {
            wp_enqueue_script( 'wprss-custom-bulk-actions' );
        }
        if ('wprss_feed_item' === $postType) {
            wp_enqueue_script( 'wprss-custom-bulk-actions-feed-item' );
        }

        // Load Heartbeat script and set dependancy for Heartbeat to ensure Heartbeat is loaded
        if ($pageBase === 'edit' && $postType === 'wprss_feed' && apply_filters('wprss_ajax_polling', TRUE) === TRUE ) {
            wp_enqueue_script( 'wprss-feed-source-table-heartbeat' );
        }

        if ($pageBase === 'wprss_feed_page_wprss-aggregator-settings') {
            wp_enqueue_script( 'wprss-admin-license-manager' );
            wp_enqueue_script( 'wprss-admin-licensing' );
        }

        if ($pageBase === 'wprss_feed_page_wprss-help') {
            wp_enqueue_script( 'wprss-admin-help' );
        }

        if ($pageBase === 'wprss_feed_page_wpra_tools') {
            wp_enqueue_script('wpra-tools');
            wp_enqueue_script('wpra-logs-tool');
            wp_enqueue_script('wpra-blacklist-tool');
            wp_enqueue_script('wpra-crons-tool');
            wp_enqueue_script('wpra-reset-tool');
        }

        if (wprss_is_help_beacon_enabled()) {
            wp_enqueue_script('wprss-hs-beacon-js');
            wp_enqueue_style('wprss-hs-beacon-css');
        }

        do_action('wprss_admin_exclusive_scripts_styles');
    }


    add_action( 'wp_enqueue_scripts', 'wprss_load_scripts' );
    /**
     * Enqueues the required scripts.
     *
     * @since 3.0
     */
    function wprss_load_scripts() {
      /*  wp_enqueue_script( 'jquery.colorbox-min', WPRSS_JS . 'jquery.colorbox-min.js', array( 'jquery' ) );
        wp_enqueue_script( 'custom', WPRSS_JS . 'custom.js', array( 'jquery', 'jquery.colorbox-min' ) );  */
        do_action( 'wprss_register_scripts' );
    } // end wprss_head_scripts_styles


    /**
     * Returns the path to the WPRSS templates directory
     *
     * @since       3.0
     * @return      string
     */
    function wprss_get_templates_dir() {
        return WPRSS_DIR . 'templates';
    }


    /**
     * Returns the URL to the WPRSS templates directory
     *
     * @since       3.0
     * @return      string
     */
    function wprss_get_templates_uri() {
        return WPRSS_URI . 'templates';
    }


    add_action( 'init', 'wprss_register_styles' );
    /**
     * Registers all WPRA styles.
     *
     * Does not enqueue anything.
     *
     * @since 3.0
     */
    function wprss_register_styles()
    {
        $version = wprss()->getVersion();

        wp_register_style( 'wprss-admin-styles', WPRSS_CSS . 'admin-styles.css', array(), $version );
        wp_register_style( 'wprss-fa', WPRSS_CSS . 'font-awesome.min.css', array(), $version );
        wp_register_style( 'wprss-admin-3.8-styles', WPRSS_CSS . 'admin-3.8.css', array(), $version );
        wp_register_style( 'wprss-admin-editor-styles', WPRSS_CSS . 'admin-editor.css', array(), $version );
        wp_register_style( 'wprss-admin-tracking-styles', WPRSS_CSS . 'admin-tracking-styles.css', array(), $version );
        wp_register_style( 'wprss-admin-general-styles', WPRSS_CSS . 'admin-general-styles.css', array(), $version );
        wp_register_style( 'wprss-hs-beacon-css', WPRSS_CSS . 'beacon.css', array(), $version );
        wp_register_style( 'jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css', array(), $version );
    }
