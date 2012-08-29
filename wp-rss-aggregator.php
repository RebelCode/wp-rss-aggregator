<?php
    /*
    Plugin Name: WP RSS Aggregator
    Plugin URI: http://www.jeangalea.com
    Description: Imports and merges multiple RSS Feeds using SimplePie
    Version: 2.0
    Author: Jean Galea
    Author URI: http://www.jeangalea.com
    License: GPLv2
    */

    /*  
    Copyright 2011-2012 Jean Galea (email : jean@jpgalea.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
    */

    /*
    @version 2.0
    @author Jean Galea <info@jeangalea.com>
    @copyright Copyright (c) 2012, Jean Galea
    @link http://www.jeangalea.com/wordpress/wp-rss-aggregator/
    @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
    */


    /**
     * Defines constants used by the plugin.
     *
     * @since 2.0
     */
    function wprss_constants() {
        
        /* Set the version number of the plugin. */
        define( 'WPRSS_VERSION', '2.0', true );

        /* Set the database version number of the plugin. */
        define( 'WPRSS_DB_VERSION', 2 );

        /* Set the plugin prefix */
        define( 'PLUGIN_PREFIX', 'wprss', true );            

        /* Set constant path to the plugin directory. */
        define( 'WPRSS_DIR', plugin_dir_path( __FILE__ ) );        

        /* Set constant URI to the plugin URL. */
        define( 'WPRSS_URI', plugin_dir_url( __FILE__ ) );        

        /* Set the constant path to the plugin's javascript directory. */
        define( 'WPRSS_JS', WPRSS_URI . trailingslashit( 'js' ), true );

        /* Set the constant path to the plugin's CSS directory. */
        define( 'WPRSS_CSS', WPRSS_URI . trailingslashit( 'css' ), true );

        /* Set the constant path to the plugin's images directory. */
        define( 'WPRSS_IMG', WPRSS_URI . trailingslashit( 'img' ), true );

        /* Set the constant path to the plugin's includes directory. */
        define( 'WPRSS_INC', WPRSS_DIR . trailingslashit( 'inc' ), true );
    }


    /**
     * Loads the initial files needed by the plugin.
     *
     * @since 2.0
     * @todo Might separate into another function admin_includes() at a later stage
     */
    function wprss_includes() {

        /* Load the activation functions file. */
        require_once ( WPRSS_INC . 'activation.php' );

        /* Load the deactivation functions file. */
        require_once ( WPRSS_INC . 'deactivation.php' );

        /* Load install, upgrade and migration code. */
        require_once ( WPRSS_INC . 'update.php' );           
        
        /* Load the shortcodes functions file. */
        require_once ( WPRSS_INC . 'shortcodes.php' );
        
        /* Load the admin functions file. */
        require_once ( WPRSS_INC . 'admin-options.php' );         

        /* Load the custom post types and taxonomies. */
        require_once ( WPRSS_INC . 'custom-post-types.php' );         

        /* Load the cron job scheduling functions. */
        require_once ( WPRSS_INC . 'cron-jobs.php' );              
    }


    /**
     * Initialise the plugin
     * 
     * @since 2.0
     * @todo fix publish_post hook to fire only on publishing of new feed sources
     */     
    add_action( 'init', 'wprss_init' );

    function wprss_init() {
        
        wprss_constants();
        wprss_includes();

        wprss_register_post_types();
        wprss_version_check();
        //wprss_add_meta_boxes();

        register_activation_hook( WPRSS_INC . 'activation.php', 'wprss_activate' );
        register_deactivation_hook( WPRSS_INC . 'deactivation.php', 'wprss_deactivate' );

        /* Hook our version check to 'init'. */
        add_action( 'init', 'wprss_version_check' );
        
        add_action( 'wp_head', 'wprss_head_scripts_styles' );   
        add_action( 'admin_enqueue_scripts', 'wprss_admin_scripts_styles' );  

        // Add meta boxes for wprss_feed post type
        add_action( 'add_meta_boxes', 'wprss_add_meta_boxes');

        // TO FIX! This should only run when a wprss_feed post is published, not a general post
        add_action( 'publish_post', 'wprss_fetch_feed_items' ); 
        
        // Set up the taxonomies
        //add_action( 'init', 'wprss_register_taxonomies' );
        wprss_schedule_truncate_posts_cron();
        wprss_schedule_fetch_feeds_cron();
    }
        

    /**
     * Insert required scripts, styles and filters on the admin side
     * 
     * @since 2.0
     */   
    function wprss_admin_scripts_styles() {
        // Only load scripts if we are on this plugin's options or settings pages (admin)
        if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'wprss-aggregator' | $_GET['page'] == 'wprss-aggregator-settings' ) ) {        
            wp_enqueue_style( 'styles', WPRSS_CSS . 'styles.css' );
        } 

        // Only load scripts if we are on wprss_feed add post or edit post screens
        $screen = get_current_screen();

        if ( ( 'post' === $screen->base || 'edit' === $screen->base ) && ( 'wprss_feed' === $screen->post_type || 'wprss_feed_item' === $screen->post_type ) ) {
            wp_enqueue_style( 'styles', WPRSS_CSS . 'styles.css' );
            if ( 'post' === $screen->base && 'wprss_feed' === $screen->post_type ) {
                // Change text on post screen from 'Enter title here' to 'Enter feed name here'
                add_filter( 'enter_title_here', function() { _e("Enter feed name here"); } );
            }
        }      
    }

    //add filter to ensure the text Feed source is displayed when user updates a feed souce
    add_filter( 'post_updated_messages', 'wprss_feed_updated_messages' );

 
    /**
     * Scripts and styles to be inserted into <head> section in front end
     * 
     * @since 2.0
     */      
    function wprss_head_scripts_styles() {
        wp_enqueue_style( 'colorbox', WPRSS_CSS . 'colorbox.css' );
        wp_enqueue_script( 'custom', WPRSS_JS .'custom.js', array('jquery') );   
        wp_enqueue_script( 'jquery.colorbox-min', WPRSS_JS .'jquery.colorbox-min.js', array('jquery') );         
    }


    /**
     * Fetches feed items from sources provided
     * 
     * @since 2.0
     */
    function wprss_fetch_feed_items() {

        // Make sure the post obj is present and post type is none other than wprss_feed
        /*
        global $post;
        if ( $post->post_type != 'wprss_feed' ) {        
            return;
        }*/

        // Get all feed sources
        $feed_sources = new WP_Query( array(
            'post_type' => 'wprss_feed',
        ) );

        if( $feed_sources->have_posts() ) {
            
            // Start by getting one feed source, we will cycle through them one by one, 
            // fetching feed items and adding them to the database in each pass
            while ( $feed_sources->have_posts() ) {                
                $feed_sources->the_post();
               
                $feed_ID = get_the_ID();
                $feed_url = get_post_meta( get_the_ID(), 'wprss_url', true );
                
                // Use the URL custom field to fetch the feed items for this source
                if( !empty( $feed_url ) ) {             
                    $feed = fetch_feed( $feed_url ); 
                    if ( !is_wp_error( $feed ) ) {
                        $items = $feed->get_items(); 
                    }
                }
            
                if ( !empty( $items ) ) {
                    // Gather the permalinks of existing feed item's related to this feed source
                    global $wpdb;
                    $existing_permalinks = $wpdb->get_col(
                        "SELECT meta_value
                        FROM $wpdb->postmeta
                        WHERE meta_key = 'wprss_item_permalink'
                        AND post_id IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_value = $feed_ID)
                        ");
                    
                    foreach ( $items as $item ) {

                        // Check if newly fetched item already present in existing feed item item, 
                        // if not insert it into wp_postsm and insert post meta.
                        if (  !( in_array( $item->get_permalink(), $existing_permalinks ) )  ) { 
                            // Create post object
                            $feed_item = array(
                                'post_title' => $item->get_title(),
                                'post_content' => '',
                                'post_status' => 'publish',
                                'post_type' => 'wprss_feed_item'
                            );                
                            $inserted_ID = wp_insert_post( $feed_item, $wp_error );
                                              
                            update_post_meta( $inserted_ID, 'wprss_item_permalink', $item->get_permalink() );
                            update_post_meta( $inserted_ID, 'wprss_item_description', $item->get_description() );                        
                            update_post_meta( $inserted_ID, 'wprss_item_date', $item->get_date( 'U' ) ); // Save as Unix timestamp format
                            update_post_meta( $inserted_ID, 'wprss_feed_id', $feed_ID);
                       } //end if
                    } //end foreach
                } // end if
            } // end $feed_sources while loop
            wp_reset_postdata(); // Restore the $post global to the current post in the main query        
        }
    }


    /**
     * Display feed items on the front end (via shortcode or function)
     * 
     * @since 2.0
     */
    function wprss_display_feed_items( $args = array() ) {
        $defaults = array(
                          'date_before'  => '<h3>',
                          'date_after'   => '</h3>',
                          'links_before' => '<ul>',
                          'links_after'  => '</ul>',
                          'link_before'  => '<li>',
                          'link_after'   => '</li>'                          
                    );

        $settings = get_option( 'wprss_settings' );
        $class = '';
        $open_setting = '';
        $follow_setting = '';

        switch ( $settings['open_dd'] ) {             
            
            case 'Lightbox' :
                $class = 'class="colorbox"'; 
                break;

            case 'New window' :
                $open_setting = 'target="_blank"';
                break;   
        }

        switch ( $settings['follow_dd'] ) { 

            case 'No follow' :
                $follow_setting = 'rel="nofollow"';
                break;
        }

        // Parse incoming $args into an array and merge it with $defaults           
        $args = wp_parse_args( $args, $defaults );
        // Declare each item in $args as its own variable
        extract( $args, EXTR_SKIP );       
        
        // Query to get all feed items for display
        $paged = get_query_var('page') ? get_query_var('page') : 1;
        $feed_items = new WP_Query( array(
            'post_type' => 'wprss_feed_item',
            'posts_per_page' => $settings['feed_limit'], 
            'orderby'  => 'meta_value', 
            'meta_key' => 'wprss_item_date', 
            'order' => 'DESC',
            'paged' => $paged,
        ) );

        // Globalize $wp_query
        global $wp_query;
        // Swap-hack
        $temp = $wp_query;
        $wp_query = null;
        $wp_query = $feed_items;        

        if( $feed_items->have_posts() ) {
            while ( $feed_items->have_posts() ) {                
                $feed_items->the_post();
                $permalink = get_post_meta( get_the_ID(), 'wprss_item_permalink', true );
                $feed_source_id = get_post_meta( get_the_ID(), 'wprss_feed_id', true );
                $source_name = get_the_title( $feed_source_id );                

                // convert from Unix timestamp
                $date = date( 'Y-m-d H:i:s', get_post_meta( get_the_ID(), 'wprss_item_date', true ) ) ;
                echo '<li><a ' . $class . $open_setting . $follow_setting . 'href=" '. $permalink . '">'. get_the_title(). ' '. '</a>'; 
                echo '<br><span class="feed-source">Source: ' . $source_name . ' | ' . $date . '</span>'; 
            }
            echo paginate_links();

            wp_reset_postdata();
            
        } else {
            echo 'No feed items found';
        }
          
        $wp_query = null; 
        $wp_query = $temp;  // Reset
}

?>