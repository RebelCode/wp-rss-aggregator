<?php
    /*
    Plugin Name: WP RSS Aggregator
    Plugin URI: http://www.jeangalea.com
    Description: Imports and merges multiple RSS Feeds using SimplePie
    Version: 1.1
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
    @version 1.1
    @author Jean Galea <info@jeangalea.com>
    @copyright Copyright (c) 2012, Jean Galea
    @link http://www.jeangalea.com/wordpress/wp-rss-aggregator/
    @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
    */


    /**
     * Defines constants used by the plugin.
     *
     * @since 1.1
     */
    function constants() {
        
        /* Set the version number of the plugin. */
        define( 'WPRSS_VERSION', '1.1', true );

        /* Set the database version number of the plugin. */
        define( 'WPRSS_DB_VERSION', 1 );

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
     * @since 1.1
     * @todo Might separate into another function admin_includes() at a later stage
     */
    function includes() {

        /* Load the activation functions file. */
        require_once ( WPRSS_INC . 'activation.php' );

        /* Load the deactivation functions file. */
        require_once ( WPRSS_INC . 'deactivation.php' );
        
        /* Load the shortcodes functions file. */
        require_once ( WPRSS_INC . 'shortcodes.php' );
        
        /* Load the admin functions file. */
        require_once ( WPRSS_INC . 'admin-options.php' );         

        /* Load the custom post types and taxonomies. */
        require_once ( WPRSS_INC . 'custom-post-types.php' );         
    }

        // Add meta boxes for wprss_feed post type
        add_action( 'add_meta_boxes', 'wprss_add_meta_boxes');
    /**
     * wprss_init()
     * Initialise the plugin
     * @since 1.2
     */ 
    
    add_action( 'init', 'wprss_init' );

    function wprss_init() {
        
        constants();
        includes();
        wprss_register_post_types();
        //wprss_add_meta_boxes();

        register_activation_hook( WPRSS_INC . 'activation.php', 'wprss_activate' );
        register_deactivation_hook( WPRSS_INC . 'deactivation.php', 'wprss_deactivate' );
        
        add_action ( 'wp_head', 'wprss_head_output' );   
        

        
        // Add meta boxes for wprss_feed post type
        //add_action( 'add_meta_boxes', 'wprss_add_meta_boxes');
        
         // Set up the taxonomies
        //add_action( 'init', 'wprss_register_taxonomies' );

        wp_enqueue_style( 'colorbox', WPRSS_CSS . 'colorbox.css' );
        wp_enqueue_script( 'jquery.colorbox-min', WPRSS_JS .'jquery.colorbox-min.js', array('jquery') );          
             
        // Only load scripts if we are on this plugin's options or settings pages (admin)
        if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'wprss-aggregator' | $_GET['page'] == 'wprss-aggregator-settings' ) ) {        
            wp_enqueue_style( 'styles', WPRSS_CSS . 'styles.css' );
        } 
   
        // Only load scripts if we are on this plugin's options page (admin)
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wprss-aggregator' ) {
           wp_enqueue_script( 'jquery' );
           wp_enqueue_script( 'add-remove', WPRSS_JS . 'add-remove.js' );
        }    
    }
 
   
    /**
     * jQuery code to trigger Colorbox for links, goes in the <head> section on frontend
     */

    function wprss_head_output() {
        echo "
        <script type='text/javascript'>
            jQuery( document ).ready( function() { 
                jQuery( 'a.colorbox' ).colorbox(
                {iframe:true, width:'80%', height:'80%'}
                );
            });
        </script>";
    }


    /**
     * Convert from field name to user-friendly name
     */ 
    
    function wprss_convert_key( $key ) { 
        if ( strpos( $key, 'feed_name_' ) === 0 ) { 
            $label = str_replace( 'feed_name_', 'Feed name ', $key );
        }
        
        else if ( strpos( $key, 'feed_url_' ) === 0 ) { 
            $label = str_replace( 'feed_url_', 'Feed URL ', $key );
        }
        return $label;        
    }
    
    
    /**
     * Get feeds and output the aggregation
     */     
        
    function wp_rss_aggregator( $args = array() ) {
        
        $defaults = array(
                          'date_before'  => '<h3>',
                          'date_after'   => '</h3>',
                          'links_before' => '<ul>',
                          'links_after'  => '</ul>',
                          'link_before'  => '<li>',
                          'link_after'   => '</li>'                          
                    );
        
        // Parse incoming $args into an array and merge it with $defaults        	
	    $args = wp_parse_args( $args, $defaults );
        // Declare each item in $args as its own variable
        extract( $args, EXTR_SKIP );        
        
        $wprss_options = get_option( 'wprss_options', 'option not found' );   
    
        foreach ( $wprss_options as $key => $value ) {            
            if ( strpos( $key, 'feed_url_' ) === 0 ) {                 
                $feed_uris[] = $value;
            } 
        }        
        
        if ( !empty( $feed_uris ) ) {           
            // update feeds every hour else serve from cache
            function wprss_hourly_feed() { return 3600; }
            add_filter( 'wp_feed_cache_transient_lifetime', 'wprss_hourly_feed' );
            $feed = fetch_feed( $feed_uris );    
        }
        else echo 'No feed defined';
        remove_filter( 'wp_feed_cache_transient_lifetime', 'wprss_hourly_feed' );
        
        $items = $feed->get_items();        
        $items_today = array();
        $items_yesterday = array();
        $items_two_days_ago = array();
        $items_older = array();
        
        
        foreach ( $items as $item ):        
            $item_date = $item->get_date('l jS F (Y-m-d)');
            if ( $item_date == date('l jS F (Y-m-d)', strtotime('today') ) ) {
                $items_today[] = $item;
            }
            else if ( $item_date == date('l jS F (Y-m-d)', strtotime('yesterday') ) ) {
                $items_yesterday[] = $item; 
            }
            else if ( $item_date == date('l jS F (Y-m-d)', strtotime('-2 days') ) ) {
                $items_two_days_ago[] = $item;
            }
            else {
                $items_older[] = $item;
            }                   
        endforeach;
        
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


        if ( !empty( $items_today ) ) { 
            echo $date_before . 'Today' . $date_after;
            echo $links_before;
            foreach ( $items_today as $item ) {                
                echo $link_before . '<a ' . $class . $open_setting . $follow_setting . 'href="' . $item->get_permalink() .'">'. $item->get_title(). ' '. '</a>'; 
                echo '<br><span class="feed-source">Source: '.$item->get_feed()->get_title()/* . ' | ' . $item->get_date('l jS F').''*/ . '</span>';
                echo $link_after;            
            }
            echo $links_after;
        }
        
        if ( !empty( $items_yesterday ) ) { 
            echo $date_before . 'Yesterday' . $date_after;
            echo $links_before;
            foreach ( $items_yesterday as $item ) {
                echo '<li><a ' . $class . $open_setting . $follow_setting . 'href="' . $item->get_permalink() .'">'. $item->get_title(). ' '. '</a>'; 
                echo '<br><span class="feed-source">Source: '.$item->get_feed()->get_title()/* . ' | ' . $item->get_date('l jS F').''*/ . '</span>';
                echo $link_after;
            }
            echo $links_after;
        }
        
        if ( !empty( $items_two_days_ago ) ) { 
            echo $date_before . '2 days ago' . $date_after;
            echo $links_before;
            foreach ( $items_two_days_ago as $item ) {
                echo '<li><a ' . $class . $open_setting . $follow_setting . 'href="' . $item->get_permalink() .'">'. $item->get_title(). ' '. '</a>'; 
                echo '<br><span class="feed-source">Source: '.$item->get_feed()->get_title()/* . ' | ' . $item->get_date('l jS F').''*/ . '</span>';
                echo $link_after;
            }
            echo $links_after;
        }
        if ( !empty( $items_older ) ) { 
            echo $date_before . 'More than 2 days ago' . $date_after;
            echo $links_before;
            foreach ( $items_older as $item ) {
                echo '<li><a ' . $class . $open_setting . $follow_setting . 'href="' . $item->get_permalink() .'">'. $item->get_title(). ' '. '</a>'; 
                echo '<br><span class="feed-source">Source: '.$item->get_feed()->get_title() . ' | ' . $item->get_date('l jS F').'</span>';
                echo $link_after;
            }           
            echo $links_after;

        }
    }
    
    // use just for testing - runs on each wp load
    //add_action( 'wp_loaded', 'wp_rss_aggregator' );


?>