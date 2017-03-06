<?php

use Interop\Container\Exception\NotFoundException as ServiceNotFoundException;
use Aventura\Wprss\Core\Model\AdminAjaxNotice\NoticeInterface;

    /**
     * Plugin debugging
     *
     * @package    WPRSSAggregator
     * @subpackage Includes
     * @since      3.0
     * @author     Jean Galea <info@jeangalea.com>
     * @copyright  Copyright(c) 2012-2015, Jean Galea
     * @link       http://www.wprssaggregator.com
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

        $operations['render-error-log'] = apply_filters(
            'wprss_render_error_log_operation',
            array(
                'nonce'     =>  null,
                'run'       =>  null,
                'redirect'  =>  'edit.php?post_type=wprss_feed&page=wprss-debugging',
                'render'    =>  'wprss_debug_render_error_log'
            )
        );

        $operations['download-error-log'] = apply_filters(
            'wprss_debug_download_error_log_operation',
            array(
                'nonce'     =>  'wprss-download-error-log',
                'run'       =>  'wprss_download_log',
                'redirect'  =>  'edit.php?post_type=wprss_feed&page=wprss-debugging',
                'render'    =>  'wprss_debug_download_log_button'
            )
        );

        $operations['clear-error-log'] = apply_filters(
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
     * Renders the Error Log.
     */
    function wprss_debug_render_error_log() {
        ?>
        <h3><?php _e( 'Error Log', WPRSS_TEXT_DOMAIN ); ?></h3>

        <textarea readonly="readonly" id="wprss-error-log-textarea"><?php echo wprss_get_log(); ?></textarea>
        <?php
    }

    /**
     * Renders the "Clear log" button
     * 
     * @since 3.9.6
     */
    function wprss_debug_clear_log_button() {
        $form_url = admin_url( 'edit.php?post_type=wprss_feed&page=wprss-debugging' ); ?>
        <form id="wprss-clear-error-log-form" action="<?php echo $form_url; ?>" method="POST" class="wprss-error-log-action">
            <?php wp_nonce_field( 'wprss-clear-error-log' ); ?>
            <button type="submit" for="wprss-clear-error-log-form" name="clear-error-log" class="button button-red">
                <i class="fa fa-trash-o"></i>
                <?php _e( 'Clear log', WPRSS_TEXT_DOMAIN ); ?>
            </button>
        </form>
        <?php
    }

    /**
     * Renders the "Download Error Log" button
     *
     * @since 4.7.8
     */
    function wprss_debug_download_log_button() {
        $form_url = admin_url( 'edit.php?post_type=wprss_feed&page=wprss-debugging' ); ?>
        <form id="wprss-download-error-log-form" action="<?php echo $form_url; ?>" method="POST" class="wprss-error-log-action">
            <?php wp_nonce_field( 'wprss-download-error-log' ); ?>
            <button type="submit" for="wprss-download-error-log-form" name="download-error-log" class="button button-primary">
                <i class="fa fa-download"></i>
                <?php _e( 'Download log', WPRSS_TEXT_DOMAIN ); ?>
            </button>
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
                '1'     =>  'debug_feeds_updating',
                '2'     =>  'debug_feeds_reimporting',
                '3'     =>  'debug_cleared_log',
                '4'		=>	'debug_settings_reset',
            )
        );
        
        ?>

        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>

            <h2><?php _e( 'Debugging', WPRSS_TEXT_DOMAIN ); ?></h2>
            <?php 
            if ( isset( $_GET['debug_message'] ))  {//&& ( check_admin_referer( 'wprss-delete-import-feed-items' ) || check_admin_referer( 'wprss-update-feed-items' ) ) ) {
                $message = $_GET['debug_message'];

                $helper = wprss()->getAdminHelper();
                foreach ( $debug_messages as $id => $noticeId) {
                    if ( $message == $id ) {
                        $noticeId = $helper->resolveValueOutput($noticeId);
                        
                        $component  = wprss()->getAdminAjaxNotices();
                        $collection = $component->getNoticeCollection();

                        try {
                            $noticeObj = $component->getNotice($noticeId);

                            if (!$noticeObj instanceof Aventura\Wprss\Core\DataObject) {
                                throw new Exception(
                                    sprintf(
                                        __('Expected notice to be a DataObject instance: %s given.', WPRSS_TEXT_DOMAIN),
                                        is_object($noticeObj)? get_class($noticeObj) : gettype($noticeObj)
                                    )
                                );
                            }
                        } catch (ServiceNotFoundException $ex) {
                            $content = trim(strip_tags($noticeId, '<strong><em><br><p>'));
                            $noticeObj = $helper->createNotice(array(
                                'content'           => $content,
                                'dismiss_mode'      => NoticeInterface::DISMISS_MODE_FRONTEND,
                            ));
                        }

                        $noticeData = $noticeObj->getData();
                        echo $collection->render_notice($collection->normalize_notice_data($noticeData));

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
