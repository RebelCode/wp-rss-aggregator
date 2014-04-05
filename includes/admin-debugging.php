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
        <h3><?php _e( 'Update All Feeds Now', 'wprss' ); ?></h3>
        <p><?php _e( 'Click the blue button to update all feed items now. This will check all feed sources for any new feed items.', 'wprss' ); ?>
            <br><?php _e( 'Existing feed items will not be modified.', 'wprss' ); ?>
        </p>
        <p><?php _e( '<strong>Note:</strong> This might take more than a few seconds if you have many feed sources.', 'wprss' ); ?></p>            
        
        <form action="edit.php?post_type=wprss_feed&page=wprss-debugging" method="post"> 
            
                <?php wp_nonce_field( 'wprss-update-feed-items' );
                submit_button( __( 'Update all feeds', 'wprss' ), 'primary', 'update-feeds', true ); ?>            
            
        </form>
        <?php
    }


    /**
     * Build the Update Feeds section
     * 
     * @since 3.4.6
     */
    function wprss_debug_reimport_feeds() {
        ?>
        <h3><?php _e( 'Delete and Re-import Feeds', 'wprss' ); ?></h3>
        <p><?php _e( 'Click the red button to delete all imported feed items and re-import them.', 'wprss' ); ?></p>
        <p><?php _e( '<em><strong>Note:</strong> This is a server-intensive process and should only be used when instructed to by support staff.</em>', 'wprss' ); ?></p>            
        
        <form action="edit.php?post_type=wprss_feed&page=wprss-debugging" method="post"> 
            
                <?php wp_nonce_field( 'wprss-delete-import-feed-items' );
                submit_button( __( 'Delete and Re-import all feeds', 'wprss' ), 'button-red', 'reimport-feeds', true  ); ?>            
            
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
        <h3><?php _e( 'Error Log', 'wprss' ); ?></h3>

        <textarea readonly="readonly" id="wprss-error-log-textarea"><?php echo wprss_get_log(); ?></textarea>

        <form action="edit.php?post_type=wprss_feed&page=wprss-debugging" method="POST"> 
            <?php wp_nonce_field( 'wprss-clear-error-log' );
            submit_button( __( 'Clear log', 'wprss' ), 'button-primary', 'error-log', true  ); ?>
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
            )
        );
        
        ?>

        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>

            <h2><?php _e( 'Debugging', 'wprss' ); ?></h2>
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

            $debug_operations = wprss_get_debug_operations();
            foreach( $debug_operations as $id => $data ) {
                if ( isset( $data['render'] ) )
                    call_user_func( $data['render'] );
            }

            do_action( 'wprss_debugging_after' );

            wprss_system_info(); ?>
        </div>
    <?php
    }       


    /**
     * Build the Help page
     * 
     * @since 4.2
     */ 
    function wprss_help_page_display() {        
        ?>

        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>

            <h2><?php _e( 'Help & Support', 'wprss' ); ?></h2>
            <h3>Documentation</h3>
            <p>In the <a href="www.wprssaggregator.com/documentation/">documentation area</a> on the WP RSS Aggregator website you will find comprehensive details on how to use the core plugin
            and all the add-ons.</p><p>There are also some videos to help you make a quick start to setting up and enjoying this plugin.</p>
            <h3>Frequently Asked Questions (FAQ)</h3>
            <p>If after going through the documentation you still have questions, please take a look at the <a href="http://www.wprssaggregator.com/faq/">FAQ page</a> on the site, we set this
            up purposely to answer the most commonly asked questions by our users.</p>
            <h3>Support Forums - Core (free version) Plugin Users Only</h3>
            <p>If you're using the free version of the plugin found on WordPress.org, you can ask questions on the <a href="http://wordpress.org/support/plugin/wp-rss-aggregator">support forum</a>.</p>
            <h3>Email Ticketing System - Premium Add-on Users Only</h3>
            <p>If you still can't find an answer to your query after reading the documentation and going through the FAQ, just <a href="http://www.wprssaggregator.com/contact/">open a support request ticket</a>.<br> 
            We'll be happy to help you out.</p>
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


    /**
     * Output admin notice that log has been cleard
     * 
     * @since 3.9.6
     */ 
    function wprss_debugging_admin_notice_clear_log() {        
        echo '<div class="updated"><p>The error log has been cleared.</p></div>';
    }