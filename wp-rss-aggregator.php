<?php
    /*
    Plugin Name: WP RSS Aggregator
    Plugin URI: http://wordpress.org/extend/plugins/wp-rss-aggregator/
    Description: Imports and merges multiple RSS Feeds using SimplePie
    Version: 2.0
    Author: Jean Galea
    Author URI: http://www.jeangalea.com
    License: GPLv3
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
    @link http://www.jeangalea.com/
    @license http://www.gnu.org/licenses/gpl.html
    */


    /**
     * Define constants used by the plugin.
     *
     * We're not checking if constants are defined before setting them, as the prefix 'wprss' pretty
     * much eliminates the possibility of them being set before. If there is a reasonable chance
     * that they would have been set earlier or by another plugin, it's better to check before 
     * setting them via if !(defined).
     *
     */

    /* Set the version number of the plugin. */
    define( 'WPRSS_VERSION', '2.0', true );

    /* Set the database version number of the plugin. */
    define( 'WPRSS_DB_VERSION', 2 );

    /* Set the plugin prefix */
    define( 'WPRSS_PREFIX', 'wprss', true );            

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
    

    /**
     * Load required files.
     */
    
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
    
    add_action( 'init', 'wprss_schedule_fetch_feeds_cron' );
    add_action( 'init', 'wprss_schedule_truncate_posts_cron' );


    add_action( 'init', 'wprss_init' );      
    /**
     * Initialise the plugin
     * 
     * @since 2.0     
     */         
    function wprss_init() {                

        register_activation_hook( WPRSS_INC . 'activation.php', 'wprss_activate' );
        register_deactivation_hook( WPRSS_INC . 'deactivation.php', 'wprss_deactivate' );
    }


    add_action( 'admin_enqueue_scripts', 'wprss_admin_scripts_styles' ); 
    /**
     * Insert required scripts, styles and filters on the admin side
     * 
     * @since 2.0
     */   
    function wprss_admin_scripts_styles() {
        // Only load scripts if we are on this plugin's options or settings pages (admin)
        if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'wprss-aggregator' || $_GET['page'] == 'wprss-aggregator-settings' ) ) {        
            wp_enqueue_style( 'styles', WPRSS_CSS . 'styles.css' );
        } 

        // Only load scripts if we are on wprss_feed add post or edit post screens
        $screen = get_current_screen();

        if ( ( 'post' === $screen->base || 'edit' === $screen->base ) && ( 'wprss_feed' === $screen->post_type || 'wprss_feed_item' === $screen->post_type ) ) {
            wp_enqueue_style( 'styles', WPRSS_CSS . 'styles.css' );
            if ( 'post' === $screen->base && 'wprss_feed' === $screen->post_type ) {
                // Change text on post screen from 'Enter title here' to 'Enter feed name here'
                add_filter( 'enter_title_here', 'wprss_change_title_text' );
            }
        }      
    }
    

    /**
     * Change title on wprss_feed post type screen
     * 
     * @since 2.0
     */  
    function wprss_change_title_text() {
        return "Enter feed name here (e.g. WP Mayor)";
    }


    add_action( 'wp_head', 'wprss_head_scripts_styles' );
    /**
     * Scripts and styles to be inserted into <head> section in front end
     * 
     * @since 2.0
     */      
    function wprss_head_scripts_styles() {
        wp_enqueue_style( 'colorbox', WPRSS_CSS . 'colorbox.css' );       
        wp_enqueue_script( 'jquery.colorbox-min', WPRSS_JS .'jquery.colorbox-min.js', array('jquery') );         
        wp_enqueue_script( 'custom', WPRSS_JS .'custom.js', array('jquery','jquery.colorbox-min') );           
    }
      

    /**
     * Fetches feed items from sources provided
     * 
     * @since 2.0
     */
    function wprss_fetch_all_feed_items( ) {            
        
            // Get all feed sources
            $feed_sources = new WP_Query( array(
                'post_type'      => 'wprss_feed',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ) );
            
            if( $feed_sources->have_posts() ) {
                /* Start by getting one feed source, we will cycle through them one by one, 
                   fetching feed items and adding them to the database in each pass */
                while ( $feed_sources->have_posts() ) {                
                    $feed_sources->the_post();
                    
                    $feed_ID = get_the_ID();
                    $feed_url = get_post_meta( get_the_ID(), 'wprss_url', true );
                    
                    // Use the URL custom field to fetch the feed items for this source
                    if( !empty( $feed_url ) ) {             
                        $feed = fetch_feed( $feed_url ); 
                        if ( !is_wp_error( $feed ) ) {
                            // Figure out how many total items there are, but limit it to 10. 
                            $maxitems = $feed->get_item_quantity(10); 

                            // Build an array of all the items, starting with element 0 (first element).
                            $items = $feed->get_items( 0, $maxitems );   
                        }
                    }

                    if ( ! empty( $items ) ) {
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
                            // if not insert it into wp_posts and insert post meta.
                            if (  ! ( in_array( $item->get_permalink(), $existing_permalinks ) )  ) { 
                                // Create post object
                                $feed_item = array(
                                    'post_title' => $item->get_title(),
                                    'post_content' => '',
                                    'post_status' => 'publish',
                                    'post_type' => 'wprss_feed_item'
                                );                
                                $inserted_ID = wp_insert_post( $feed_item );
                                                  
                                update_post_meta( $inserted_ID, 'wprss_item_permalink', $item->get_permalink() );
                                update_post_meta( $inserted_ID, 'wprss_item_description', $item->get_description() );                        
                                update_post_meta( $inserted_ID, 'wprss_item_date', $item->get_date( 'U' ) ); // Save as Unix timestamp format
                                update_post_meta( $inserted_ID, 'wprss_feed_id', $feed_ID);
                           } //end if
                        } //end foreach
                    } // end if
                } // end $feed_sources while loop
                wp_reset_postdata(); // Restore the $post global to the current post in the main query        
           // } // end if
        } // end if
    } 


    add_action('wp_insert_post', 'wprss_fetch_feed_items'); 
    /**
     * Fetches feed items from sources provided
     * 
     * @since 2.0
     */
    function wprss_fetch_feed_items( $post_id ) {            
        
        // Get current post that triggered the hook, $post_id passed via the hook
        $post = get_post( $post_id );
                
        if( ( $post->post_type == 'wprss_feed') && ( $post->post_status == 'publish' ) ) { 
        
            // Get all feed sources
            $feed_sources = new WP_Query( array(
                'post_type'      => 'wprss_feed',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ) );
           
            if( $feed_sources->have_posts() ) {
                // Start by getting one feed source, we will cycle through them one by one, 
                // fetching feed items and adding them to the database in each pass
                while ( $feed_sources->have_posts() ) {                
                    $feed_sources->the_post();
                    
                    $feed_ID = get_the_ID();
                    $feed_url = get_post_meta( get_the_ID(), 'wprss_url', true );
                    
                    // Use the URL custom field to fetch the feed items for this source
                    if( ! empty( $feed_url ) ) {             
                        $feed = fetch_feed( $feed_url ); 
                        if ( ! is_wp_error( $feed ) ) {
                            // Figure out how many total items there are, but limit it to 10. 
                            $maxitems = $feed->get_item_quantity(10); 

                            // Build an array of all the items, starting with element 0 (first element).
                            $items = $feed->get_items(0, $maxitems);                             
                        }
                    }

                    if ( ! empty( $items ) ) {
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
                            if (  ! ( in_array( $item->get_permalink(), $existing_permalinks ) )  ) { 
                                // Create post object
                                $feed_item = array(
                                    'post_title' => $item->get_title(),
                                    'post_content' => '',
                                    'post_status' => 'publish',
                                    'post_type' => 'wprss_feed_item'
                                );                
                                $inserted_ID = wp_insert_post( $feed_item );
                                                  
                                update_post_meta( $inserted_ID, 'wprss_item_permalink', $item->get_permalink() );
                                update_post_meta( $inserted_ID, 'wprss_item_description', $item->get_description() );                        
                                update_post_meta( $inserted_ID, 'wprss_item_date', $item->get_date( 'U' ) ); // Save as Unix timestamp format
                                update_post_meta( $inserted_ID, 'wprss_feed_id', $feed_ID);
                           } //end if
                        } //end foreach
                    } // end if
                } // end $feed_sources while loop
                wp_reset_postdata(); // Restore the $post global to the current post in the main query        
            } // end if
        } // end if
    }        


    /**
     * Display feed items on the front end (via shortcode or function)
     * 
     * @since 2.0
     */
    function wprss_display_feed_items( $args = array() ) {
        $defaults = array(
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
        $feed_items = new WP_Query( array(
            'post_type'      => 'wprss_feed_item',
            'posts_per_page' => $settings['feed_limit'], 
            'orderby'        => 'meta_value', 
            'meta_key'       => 'wprss_item_date', 
            'order'          => 'DESC',
        ) );

        if( $feed_items->have_posts() ) {
            echo "$links_before\n";
            while ( $feed_items->have_posts() ) {                
                $feed_items->the_post();
                $permalink = get_post_meta( get_the_ID(), 'wprss_item_permalink', true );
                $feed_source_id = get_post_meta( get_the_ID(), 'wprss_feed_id', true );
                $source_name = get_the_title( $feed_source_id );                

                // convert from Unix timestamp        
                $date = date( 'Y-m-d', intval( get_post_meta( get_the_ID(), 'wprss_item_date', true ) ) );
                echo "\t\t".'<li><a ' . $class . $open_setting . ' ' . $follow_setting . ' href="'. $permalink . '">'. get_the_title(). '</a><br>' . "\n"; 
                echo "\t\t".'<span class="feed-source">Source: ' . $source_name . ' | ' . $date . '</span></li>'. "\n\n"; 
            }
            echo "\t\t $links_after";
            echo paginate_links();

            wp_reset_postdata();
            
        } else {
            echo 'No feed items found';
        }
    }

    
    add_action( 'trash_wprss_feed', 'wprss_delete_feed_items' );
    /**
     * Delete feed items on trashing of corresponding feed source
     * 
     * @since 2.0
     */    
    function wprss_delete_feed_items() {
        global $post;
  
        $args = array(
                'post_type'      => 'wprss_feed_item',
                'meta_key'       => 'wprss_feed_id',                  
                'meta_value_num' => $post->ID,      
                'posts_per_page' => -1           
        );
        
        $feed_items = new WP_Query( $args );  

        if ( $feed_items->have_posts() ) :
            while ( $feed_items->have_posts() ) : $feed_items->the_post();
                $postid = get_the_ID();

                $purge = wp_delete_post( $postid, true );                
            endwhile;
        endif;
 
        wp_reset_postdata();
    }    

 
    /**
     * Delete old feed items from the database to avoid bloat
     * 
     * @since 2.0
     */
    function wprss_truncate_posts() {
        global $wpdb;

        // Set your threshold of max posts and post_type name
        $threshold = 50;
        $post_type = 'wprss_feed_item';

        // Query post type
        // $wpdb query allows me to select specific columns instead of grabbing the entire post object.
        $query = "
            SELECT ID, post_title FROM $wpdb->posts 
            WHERE post_type = '$post_type' 
            AND post_status = 'publish' 
            ORDER BY post_modified DESC
        ";
        $results = $wpdb->get_results( $query );

        // Check if there are any results
        if ( count( $results ) ){
            foreach ( $results as $post ) {
                $i++;

                // Skip any posts within our threshold
                if ( $i <= $threshold )
                    continue;

                // Let the WordPress API do the heavy lifting for cleaning up entire post trails
                $purge = wp_delete_post( $post->ID, true );
            }
        }
    }    