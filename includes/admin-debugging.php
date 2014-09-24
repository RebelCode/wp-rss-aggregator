<?php
    /**
     * Plugin debugging
     *
     * @package    WPRSSAggregator
     * @subpackage Includes
     * @since      3.0
     * @author     Jean Galea <info@jeangalea.com>
     * @copyright  Copyright(c) 2012-2013, Jean Galea
     * @link       http://www.wpmayor.com
     * @license    http://www.gnu.org/licenses/gpl.html
     */

    /*
    //allow redirection, even if my theme starts to send output to the browser
    add_action( 'admin_init', 'wprss_do_output_buffer' );
    function wprss_do_output_buffer() {
        //ob_start();
    }*/
    

    /**
     * Returns the debugging operations array
     * 
     * @since 3.4.6
     */
    function wprss_get_debug_operations() {
        $operations = apply_filters(
            'wprss_debug_operations',
            array(
                'update-feeds' => array(
                    'nonce'     =>  'wprss-update-feed-items',
                    'run'       =>  'wprss_fetch_insert_all_feed_items',
                    'redirect'  =>  'edit.php?post_type=wprss_feed&page=wprss-debugging&debug_message=1',
                    'render'    =>  'wprss_debug_update_feeds',
                ),

                'reimport-feeds' => array(
                    'nonce'     =>  'wprss-delete-import-feed-items',
                    'run'       =>  'wprss_feed_reset',
                    'redirect'  =>  'edit.php?post_type=wprss_feed&page=wprss-debugging&debug_message=2',
                    'render'    =>  'wprss_debug_reimport_feeds',
                ),

            )
        );

        $operations['error-log'] = apply_filters(
            'wprss_debug_error_log_operation',
            array(
                'nonce'     =>  'wprss-clear-error-log',
                'run'       =>  'wprss_clear_log',
                'redirect'  =>  'edit.php?post_type=wprss_feed&page=wprss-debugging&debug_message=3',
                'render'    =>  'wprss_debug_clear_log_button'
            )
        );

		$operations ['restore-settings'] = apply_filters(
			'wprss_debug_restore_settings_operation',
			array(
				'nonce'     =>  'wprss-restore-settings',
				'run'       =>  'wprss_restore_settings',
				'redirect'  =>  'edit.php?post_type=wprss_feed&page=wprss-debugging&debug_message=4',
				'render'    =>  'wprss_debug_restore_settings',
				'pos'		=>	'bottom'
			)
		);

        return $operations;
    }


    add_action( 'admin_init', 'wprss_debug_operations' );
    /**
     * Performs debug operations, depending on the POST request.
     *
     * @since 3.3
     */
    function wprss_debug_operations(){

        // Define the debugging operations
        $debug_operations = wprss_get_debug_operations();

        // Check which of the operations needs to be run
        foreach ( $debug_operations as $id => $operation ) {
            // If page loading after having clicked 'Update all fields'
            if ( isset( $_POST[ $id ] ) && check_admin_referer( $operation['nonce'] ) ) { 
                call_user_func( $operation['run'] );
                wp_redirect( $operation['redirect'] );
                break;        
            }
        }
    }


    /**
     * Build the Update Feeds section
     * 
     * @since 3.4.6
     */
    function wprss_debug_update_feeds() {
        ?>
        <h3><?php _e( 'Update All Feeds Now', WPRSS_TEXT_DOMAIN ); ?></h3>
        <p><?php _e( 'Click the blue button to update all active feed items now. This will check all feed sources for any new feed items.', WPRSS_TEXT_DOMAIN ); ?>
            <br><?php _e( 'Existing feed items will not be modified.', WPRSS_TEXT_DOMAIN ); ?>
        </p>
        <p><?php _e( '<strong>Note:</strong> This might take more than a few seconds if you have many feed sources.', WPRSS_TEXT_DOMAIN ); ?></p>            
        
        <form action="edit.php?post_type=wprss_feed&page=wprss-debugging" method="post"> 
            
                <?php wp_nonce_field( 'wprss-update-feed-items' );
                submit_button( __( 'Update all feeds', WPRSS_TEXT_DOMAIN ), 'primary', 'update-feeds', true ); ?>            
            
        </form>
        <?php
    }


    /**
     * Build the Delete and Re-Import Feeds section
     * 
     * @since 3.4.6
     */
    function wprss_debug_reimport_feeds() {
        ?>
        <h3><?php _e( 'Delete and Re-import Feeds', WPRSS_TEXT_DOMAIN ); ?></h3>
        <p><?php _e( 'Click the red button to delete all imported feed items and re-import them.', WPRSS_TEXT_DOMAIN ); ?></p>
        <p><?php _e( '<em><strong>Note:</strong> This is a server-intensive process and should only be used when instructed to by support staff.</em>', WPRSS_TEXT_DOMAIN ); ?></p>            
        
        <form action="edit.php?post_type=wprss_feed&page=wprss-debugging" method="post"> 
            
                <?php wp_nonce_field( 'wprss-delete-import-feed-items' );
                submit_button( __( 'Delete and Re-import all feeds', WPRSS_TEXT_DOMAIN ), 'button-red', 'reimport-feeds', true  ); ?>            
            
        </form>
        <?php
    }


	/**
     * Render the restore settings button
     * 
     * @since 4.4
     */
    function wprss_debug_restore_settings() {
        ?>
        <h3><?php _e( 'Restore Default Settings', WPRSS_TEXT_DOMAIN ); ?></h3>
        <p><?php _e( 'Click the red button to reset the plugin settings to default.', WPRSS_TEXT_DOMAIN ); ?></p>
        <p><?php _e( '<em><strong>Note:</strong> This cannot be undone. Once the settings have been reset, your old settings cannot be restored.</em>', WPRSS_TEXT_DOMAIN ); ?></p>
        
        <form action="edit.php?post_type=wprss_feed&page=wprss-debugging" method="post"> 
            
                <?php wp_nonce_field( 'wprss-restore-settings' );
                submit_button( __( 'Restore Default Settings', WPRSS_TEXT_DOMAIN ), 'button-red', 'restore-settings', true  ); ?>            
            
        </form>
        <?php
    }


    /**
     * Renders the Clear Log button
     * 
     * @since 3.9.6
     */
    function wprss_debug_clear_log_button() {
        ?>
        <h3><?php _e( 'Error Log', WPRSS_TEXT_DOMAIN ); ?></h3>

        <textarea readonly="readonly" id="wprss-error-log-textarea"><?php echo wprss_get_log(); ?></textarea>

        <form action="edit.php?post_type=wprss_feed&page=wprss-debugging" method="POST"> 
            <?php wp_nonce_field( 'wprss-clear-error-log' );
            submit_button( __( 'Clear log', WPRSS_TEXT_DOMAIN ), 'button-primary', 'error-log', true  ); ?>
        </form>

        <?php
    }


    /**
     * Build the debugging page
     * 
     * @since 3.0
     */ 
    function wprss_debugging_page_display() {
        $debug_messages = apply_filters(
            'wprss_debug_messages',
            array(
                '1'     =>  'wprss_debugging_admin_notice_update_feeds',
                '2'     =>  'wprss_debugging_admin_notice_reimport_feeds',
                '3'     =>  'wprss_debugging_admin_notice_clear_log',
				'4'		=>	'wprss_debugging_admin_notice_reset_settings'
            )
        );
        
        ?>

        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>

            <h2><?php _e( 'Debugging', WPRSS_TEXT_DOMAIN ); ?></h2>
            <?php 
            if ( isset( $_GET['debug_message'] ))  {//&& ( check_admin_referer( 'wprss-delete-import-feed-items' ) || check_admin_referer( 'wprss-update-feed-items' ) ) ) {
                $message = $_GET['debug_message'];

                foreach ( $debug_messages as $id => $callback) {
                    if ( $message == $id ) {
                        call_user_func( $callback );
                        break;
                    }
                }  
            }

            do_action( 'wprss_debugging_before' );

			$bottom = array();
            $debug_operations = wprss_get_debug_operations();
            foreach( $debug_operations as $id => $data ) {
                if ( !isset( $data['render'] ) ) continue;
				$pos = isset( $data['pos'] ) ? $data['pos'] : 'normal';
				if ( $pos == 'normal' ) {
                    call_user_func( $data['render'] );
				} elseif( $pos == 'bottom' ) {
					$bottom[$id] = $data;
				}
            }

            do_action( 'wprss_debugging_after' );

            wprss_system_info();
			
			if ( count($bottom) > 0 ) {
				foreach( $bottom as $id => $data ) {
					if ( !isset( $data['render'] ) ) continue;
					call_user_func( $data['render'] );
				}
			}
			
			?>
        </div>
    <?php
    }       


    /**
     * Output admin notice that feeds have been updated successfully
     * 
     * @since 3.0
     */ 
     function wprss_debugging_admin_notice_update_feeds() {
		?><div class="updated"><p><?php _e( 'Feeds are being updated in the background.', WPRSS_TEXT_DOMAIN ) ?></p></div><?php
    }

    /**
     * Output admin notice that feeds have been deleted and re-imported successfully
     * 
     * @since 3.0
     */ 
    function wprss_debugging_admin_notice_reimport_feeds() {
		?><div class="updated"><p><?php _e( 'Feeds deleted and are being re-imported in the background.', WPRSS_TEXT_DOMAIN ) ?></p></div><?php
    }


    /**
     * Output admin notice that log has been cleard
     * 
     * @since 3.9.6
     */ 
    function wprss_debugging_admin_notice_clear_log() {
		?><div class="updated"><p><?php _e( 'The error log has been cleared.', WPRSS_TEXT_DOMAIN ) ?></p></div><?php
    }

	
	/**
     * Output admin notice that log has been cleard
     * 
     * @since 4.4
     */ 
    function wprss_debugging_admin_notice_reset_settings() {
		?><div class="updated"><p><?php _e( 'The plugin settings have been reset to default.', WPRSS_TEXT_DOMAIN ) ?></p></div><?php
    }


	/**
	 * Resets the plugin settings to default
	 * 
	 * @since 4.4
	 */
	function wprss_restore_settings() {
		// Action Hook
		do_action( 'wprss_before_restore_settings' );
		
		// Prepare the settings to reset
		$settings_to_restore = apply_filters(
			'wprss_settings_to_restore',
			array(
				'wprss_settings_general',
				'wprss_settings_notices',
				'wprss_addon_notices',
				'wprss_pwsv',
				'wprss_db_version'
			)
		);
		// Delete the settings
		foreach( $settings_to_restore as $setting ) {
			delete_option( $setting );
		}
		
		// Action Hook
		do_action( 'wprss_after_restore_settings' );
	}
