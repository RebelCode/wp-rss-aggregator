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
     
    
    add_action( 'admin_menu', 'wprss_register_menu_pages' );
    /**
     * Register menu and submenus
     * @since 2.0
     */ 
    
    // Add the admin options pages as submenus to the Feed CPT   
    function wprss_register_menu_pages() {        
          
        //create submenu items        
        add_submenu_page( 'edit.php?post_type=wprss_feed', __( 'WP RSS Aggregator Settings', 'wprss' ), __( 'Settings', 'wprss' ), 'manage_options', 'wprss-aggregator-settings', 'wprss_settings_page_display' );             
        add_submenu_page( 'edit.php?post_type=wprss_feed', __( 'Export & Import Settings', 'wprss' ), __( 'Import & Export', 'wprss' ), 'manage_options', 'wprss-import-export-settings', 'wprss_import_export_settings_page_display' );                     
        add_submenu_page( 'edit.php?post_type=wprss_feed', __( 'Debugging', 'wprss' ), __( 'Debugging', 'wprss' ), 'manage_options', 'wprss-debugging', 'wprss_debugging_page_display' );                             
    }


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
            $classes .= 'action-'.$_GET['action'];
        }
        // Current post ID
        if ( is_admin() && isset($_GET['post']) ) {
            $classes .= ' ';
            $classes .= 'post-'.$_GET['post'];
        }
        // New post type & listing page
        if ( isset($_GET['post_type']) ) $post_type = $_GET['post_type'];
        if ( isset($post_type) ) {
            $classes .= ' ';
            $classes .= 'post-type-'.$post_type;
        }
        // Editting a post type
        if ( isset( $_GET['post'] ) ) {
            $post_query = $_GET['post'];
        }
        if ( isset($post_query) ) {
            $current_post_edit = get_post($post_query);
            $current_post_type = $current_post_edit->post_type;
            if ( !empty($current_post_type) ) {
                $classes .= ' ';
                $classes .= 'post-type-'.$current_post_type;
            }
        }
        // Return the $classes array
        return $classes;
    }


    /**
     * Change title on wprss_feed post type screen
     * 
     * @since  2.0
     * @return void
     */  
    function wprss_change_title_text() {
        return __( 'Enter feed name here (e.g. WP Mayor)', 'wprss' );
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
        if ( $plugin_file == plugin_basename( __FILE__ ) ) {
            $settings_link = '<a href="' . get_admin_url() . 'edit.php?post_type=wprss_feed&page=wprss-aggregator-settings">' . __("Settings") . '</a>';
            array_unshift( $action_links, $settings_link );
        }
        return $action_links;
    }        
