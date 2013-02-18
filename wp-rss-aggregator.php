<?php
    /*
    Plugin Name: WP RSS Aggregator
    Plugin URI: http://www.wprssaggregator.com
    Description: Imports and merges multiple RSS Feeds using SimplePie
    Version: 2.2.3
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
    @version 2.2.3
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
    define( 'WPRSS_VERSION', '2.2.3', true );

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

    /* Load the custom post types and taxonomies. */
    require_once ( WPRSS_INC . 'custom-post-types.php' );         

    /* Load the cron job scheduling functions. */
    require_once ( WPRSS_INC . 'cron-jobs.php' ); 

    /* Load the admin functions file. */
    require_once ( WPRSS_INC . 'admin.php' );         

    /* Load the admin options functions file. */
    require_once ( WPRSS_INC . 'admin-options.php' );             

    /* Load the settings import/export file */
    require_once ( WPRSS_INC . 'admin-import-export.php' ); 

    /* Load the feed processing functions file */
    require_once ( WPRSS_INC . 'feed-processing.php' );   

    /* Load the feed processing functions file */
    require_once ( WPRSS_INC . 'feed-display.php' );   


    add_action( 'init', 'wprss_init' );      
    /**
     * Initialise the plugin
     * 
     * @since 2.0     
     */         
    function wprss_init() {                
        register_activation_hook( WPRSS_INC . 'activation.php', 'wprss_activate' );
        register_deactivation_hook( WPRSS_INC . 'deactivation.php', 'wprss_deactivate' );
        do_action( 'wprss_init' );
    } // end wprss_int


    add_action( 'plugins_loaded', 'wprss_load_textdomain' );
    /**
     * Loads the plugin's translated strings.
     * 
     * @since 2.1     
     */  
    function wprss_load_textdomain() { 
        load_plugin_textdomain( 'wprss', false, plugin_basename( __FILE__ ) . '/lang/' );
    }


    add_action( 'admin_enqueue_scripts', 'wprss_admin_scripts_styles' ); 
    /**
     * Insert required scripts, styles and filters on the admin side
     * 
     * @since 2.0
     */   
    function wprss_admin_scripts_styles() {

        // Only load scripts if we are on this plugin's options or settings pages (admin)
        if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'wprss-aggregator' || $_GET['page'] == 'wprss-aggregator-settings' || $_GET['page'] == 'wprss-import-export-settings' ) ) {        
            wp_enqueue_style( 'styles', WPRSS_CSS . 'styles.css' );                      
        } 

        // Only load scripts if we are on wprss_feed add post or edit post screens
        $screen = get_current_screen();

        if ( ( 'post' === $screen->base || 'edit' === $screen->base ) && ( 'wprss_feed' === $screen->post_type || 'wprss_feed_item' === $screen->post_type ) ) {
            wp_enqueue_style( 'styles', WPRSS_CSS . 'styles.css' );
            wp_enqueue_script( 'admin-custom', WPRSS_JS .'admin-custom.js', array('jquery') );
            if ( 'post' === $screen->base && 'wprss_feed' === $screen->post_type ) {
                // Change text on post screen from 'Enter title here' to 'Enter feed name here'
                add_filter( 'enter_title_here', 'wprss_change_title_text' );
            }
        } 

        do_action( 'wprss_admin_scripts_styles' );
    } // end wprss_admin_scripts_styles


    /**
     * Change title on wprss_feed post type screen
     * 
     * @since 2.0
     */  
    function wprss_change_title_text() {
        return __( 'Enter feed name here (e.g. WP Mayor)', 'wprss' );
    } // end wprss_change_title_text


    add_action( 'wp_head', 'wprss_head_scripts_styles' );
    /**
     * Scripts and styles to be inserted into <head> section in front end
     * 
     * @since 2.0
     */      
    function wprss_head_scripts_styles() {
        wp_enqueue_style( 'colorbox', WPRSS_CSS . 'colorbox.css' );       
        wp_enqueue_script( 'jquery.colorbox-min', WPRSS_JS . 'jquery.colorbox-min.js', array( 'jquery' ) );         
        wp_enqueue_script( 'custom', WPRSS_JS . 'custom.js', array( 'jquery', 'jquery.colorbox-min' ) );  
        do_action( 'wprss_head_scripts_styles' );         
    } // end wprss_head_scripts_styles


    /**
     * Limits a phrase/content to a defined number of words
     * 
     * @since 2.3
     */
    function limit_words( $words, $limit, $append = '' ) {
           // Add 1 to the specified limit becuase arrays start at 0
           $limit = $limit + 1;
           // Store each individual word as an array element
           // Up to the limit
           $words = explode( ' ', $words, $limit );
           // Shorten the array by 1 because that final element will be the sum of all the words after the limit
           array_pop( $words );
           // Implode the array for output, and append an ellipse
           $words = implode( ' ', $words ) . $append;
           // Return the result
           return $words;
    } // end limit_words

