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
    

    add_action( 'init', 'wprss_debug_operations' );
    /**
     * Performs debug operations, depending on the POST request.
     *
     * @since 3.3
     */
    function wprss_debug_operations(){
        // If page loading after having clicked 'Update all fields'
        if ( isset( $_POST['update-feeds'] ) && check_admin_referer( 'wprss-update-feed-items' ) ) { 
            wprss_fetch_insert_all_feed_items();
            wp_redirect( "edit.php?post_type=wprss_feed&page=wprss-debugging&debug_message=1" );           
        }

        // If page loading after having clicked 'Delete and re-import all fields'
        else if ( isset( $_POST['reimport-feeds'] ) && check_admin_referer( 'wprss-delete-import-feed-items' ) ) { 
            wprss_feed_reset();
            wp_redirect( "edit.php?post_type=wprss_feed&page=wprss-debugging&debug_message=2" );
        }
    }

    /**
     * Build the debugging page
     * 
     * @since 3.0
     */ 
    function wprss_debugging_page_display() {             
        ?>

        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>

            <h2><?php _e( 'Debugging', 'wprss' ); ?></h2>
            <?php 
            if ( isset( $_GET['debug_message'] ))  {//&& ( check_admin_referer( 'wprss-delete-import-feed-items' ) || check_admin_referer( 'wprss-update-feed-items' ) ) ) {
                $message = $_GET['debug_message'];

                switch ( $message ) {
                    case 1 : 
                        wprss_debugging_admin_notice_update_feeds();
                        break;
                    case 2 :                 
                        wprss_debugging_admin_notice_reimport_feeds();
                        break;
                }    
            } ?>
            <h3><?php _e( 'Update All Feeds Now', 'wprss' ); ?></h3>
            <p><?php _e( 'Click the blue button to update all feed items now. This will check all feed sources for any new feed items.', 'wprss' ); ?>
                <br><?php _e( 'Existing feed items will not be modified.', 'wprss' ); ?>
            </p>
            <p><?php _e( '<strong>Note:</strong> This might take more than a few seconds if you have many feed sources.', 'wprss' ); ?></p>            
            
            <form action="edit.php?post_type=wprss_feed&page=wprss-debugging" method="post"> 
                
                    <?php wp_nonce_field( 'wprss-update-feed-items' );
                    submit_button( __( 'Update all feeds', 'wprss' ), 'primary', 'update-feeds', true ); ?>            
                
            </form>

            <h3><?php _e( 'Delete and Re-import Feeds', 'wprss' ); ?></h3>
            <p><?php _e( 'Click the red button to delete all imported feed items and re-import them.', 'wprss' ); ?></p>
            <p><?php _e( '<em><strong>Note:</strong> This is a server-intensive process and should only be used when instructed to by support staff.</em>', 'wprss' ); ?></p>            
            
            <form action="edit.php?post_type=wprss_feed&page=wprss-debugging" method="post"> 
                
                    <?php wp_nonce_field( 'wprss-delete-import-feed-items' );
                    submit_button( __( 'Delete and Re-import all feeds', 'wprss' ), 'button-red', 'reimport-feeds', true  ); ?>            
                
            </form> 
            <?php wprss_system_info(); ?>       
        </div>
    <?php
    }           

    /**
     * Output admin notice that feeds have been updated successfully
     * 
     * @since 3.0
     */ 
     function wprss_debugging_admin_notice_update_feeds() {        
        echo '<div class="updated"><p>Feeds are being updated in the background.</p></div>';
    }

    /**
     * Output admin notice that feeds have been deleted and re-imported successfully
     * 
     * @since 3.0
     */ 
    function wprss_debugging_admin_notice_reimport_feeds() {        
        echo '<div class="updated"><p>Feeds deleted and are being re-imported in the background.</p></div>';
    }