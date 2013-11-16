<?php 

    /**
     * Serves up a notice to leave a review for this plugin
     * 
     * @link http://wp.tutsplus.com/tutorials/creative-coding/a-primer-on-ajax-in-the-wordpress-dashboard-requesting-and-responding/
     * @link http://wptheming.com/2011/08/admin-notices-in-wordpress/
     * 
     * @since 3.0
     * 
     */


    add_action( 'admin_init', 'wprss_admin_notice' );
    /**
     * Serves up a notice to leave a review for this plugin
     * 
     * @since 3.0
     */    
    function wprss_admin_notice() {
        global $pagenow, $typenow;
        if ( empty( $typenow ) && !empty( $_GET['post'] ) ) {
          $post = get_post( $_GET['post'] );
          if ( $post !== NULL && !is_wp_error( $post ) )
            $typenow = $post->post_type;
        }
        $notices_settings = get_option( 'wprss_settings_notices' ); 

        // Display the admin notice only if it hasn't been hidden and we are on a screen of WP RSS Aggregator
        if ( ( false == $notices_settings ) && ( ( $typenow == 'wprss_feed' ) || ( $typenow == 'wprss_feed_item' ) ) ) { 
            add_action( 'admin_notices', 'wprss_display_admin_notice' );
        } 

    } 

        
    /**
     * Renders the administration notice. Also renders a hidden nonce used for security when processing the Ajax request.
     * 
     * @since 3.0
     */
    function wprss_display_admin_notice() {

        $html = '<div id="ajax-notification" class="updated">';
            $html .= '<p>';
            $html .= __( 'Did you know that you can get more RSS features? Excerpts, thumbnails, keyword filtering, importing into posts and more... Check out the <a target="_blank" href="http://www.wprssaggregator.com/extensions"><strong>extensions</strong></a> page.
                     <a href="javascript:;" id="dismiss-ajax-notification" style="float:right;">Dismiss this notification</a>', 'wprss' );
            $html .= '</p>';
            $html .= '<span id="ajax-notification-nonce" class="hidden">' . wp_create_nonce( 'ajax-notification-nonce' ) . '</span>';
        $html .= '</div><!-- /.updated -->';
        
        echo $html;        
    } 
    
    
    add_action( 'wp_ajax_wprss_hide_admin_notification', 'wprss_hide_admin_notification' );    
    /**
     * JavaScript callback used to hide the administration notice when the 'Dismiss' anchor is clicked on the front end.
     * 
     * @since 3.0
     */
    function wprss_hide_admin_notification() {
        
        // First, check the nonce to make sure it matches what we created when displaying the message. 
        // If not, we won't do anything.
        if( wp_verify_nonce( $_REQUEST['nonce'], 'ajax-notification-nonce' ) ) {
            
            // If the update to the option is successful, send 1 back to the browser;
            // Otherwise, send 0.
            $general_settings = get_option( 'wprss_settings_notices' ); 
            $general_settings = true;

            if( update_option( 'wprss_settings_notices', $general_settings ) ) {
                die( '1' );
            } else {
                die( '0' );
            }             
        }        
    }



    /**
     * Checks if the addon notices option exists in the database, and creates it
     * if it does not.
     * 
     * @return The addon notices option
     * @since 3.4.2
     */
    function wprss_check_addon_notice_option() {
        $option = get_option( 'wprss_addon_notices' );
        if ( $option === FALSE ) {
            update_option( 'wprss_addon_notices', array() );
            return array();
        }
        return $option;
    }



    /**
     * This function is called through AJAX to dismiss a particular addon notification.
     * 
     * @since 3.4.2
     */
    function wprss_dismiss_addon_notice() {
        $addon = ( isset( $_POST['addon'] ) === TRUE )? $_POST['addon'] : null;
        if ( $addon === null ) {
            echo 'false';
            die();
        }
        $notice = ( isset( $_POST['notice'] ) === TRUE )? $_POST['notice'] : null;
        if ( $notice === null ){
            echo 'false';
            die();
        }

        $notices = wprss_check_addon_notice_option();
        if ( isset( $notices[$addon] ) === FALSE ) {
            $notices[$addon] = array();
        }
        if ( isset( $notices[$addon][$addon] ) === FALSE ) {
            $notices[$addon][$notice] = '1';
        }
        update_option( 'wprss_addon_notices', $notices );
        echo 'true';

        die();
    }

    add_action( 'wp_ajax_wprss_dismiss_addon_notice', 'wprss_dismiss_addon_notice' );




    /**
     * AJAX action for the tracking pointer
     * 
     * @since 3.6
     */
    function wprss_tracking_ajax_opt() {
        if ( isset( $_POST['opted'] ) ){
            $opted = $_POST['opted'];
            $settings = get_option( 'wprss_settings_general' );
            $settings['tracking'] = $opted;
            update_option( 'wprss_settings_general', $settings );
        }
        die();
    }

    add_action( 'wp_ajax_wprss_tracking_ajax_opt', 'wprss_tracking_ajax_opt' );