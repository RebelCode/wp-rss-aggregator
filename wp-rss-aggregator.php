<?php
    /*
    Plugin Name: WP RSS Aggregator
    Plugin URI: http://www.wpmayor.com
    Description: Imports and aggregates multiple RSS Feeds using SimplePie
    Version: 3.1
    Author: Jean Galea
    Author URI: http://www.wpmayor.com
    License: GPLv3
    */

    /*  
    Copyright 2012-2013 Jean Galea (email : info@jeangalea.com)
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

    /**
     * @package   WPRSSAggregator
     * @version   3.1
     * @since     1.0
     * @author    Jean Galea <info@jeangalea.com>
     * @copyright Copyright (c) 2012-2013, Jean Galea
     * @link      http://www.wpmayor.com/
     * @license   http://www.gnu.org/licenses/gpl.html
     */

    /**
     * Define constants used by the plugin.
     */

    // Set the version number of the plugin. 
    if( !defined( 'WPRSS_VERSION' ) )
        define( 'WPRSS_VERSION', '3.1', true );

    // Set the database version number of the plugin. 
    if( !defined( 'WPRSS_DB_VERSION' ) )
        define( 'WPRSS_DB_VERSION', 4 );    

    // Set the plugin prefix 
    if( !defined( 'WPRSS_PREFIX' ) )
        define( 'WPRSS_PREFIX', 'wprss', true );            

    // Set constant path to the plugin directory. 
    if( !defined( 'WPRSS_DIR' ) )
        define( 'WPRSS_DIR', plugin_dir_path( __FILE__ ) );        

    // Set constant URI to the plugin URL. 
    if( !defined( 'WPRSS_URI' ) )
        define( 'WPRSS_URI', plugin_dir_url( __FILE__ ) );        

    // Set the constant path to the plugin's javascript directory. 
    if( !defined( 'WPRSS_JS' ) )
        define( 'WPRSS_JS', WPRSS_URI . trailingslashit( 'js' ), true );

    // Set the constant path to the plugin's CSS directory. 
    if( !defined( 'WPRSS_CSS' ) )
        define( 'WPRSS_CSS', WPRSS_URI . trailingslashit( 'css' ), true );

    // Set the constant path to the plugin's images directory. 
    if( !defined( 'WPRSS_IMG' ) )
        define( 'WPRSS_IMG', WPRSS_URI . trailingslashit( 'images' ), true );

    // Set the constant path to the plugin's includes directory. 
    if( !defined( 'WPRSS_INC' ) )
        define( 'WPRSS_INC', WPRSS_DIR . trailingslashit( 'includes' ), true );
    

    /**
     * Load required files.
     */

    /* Load install, upgrade and migration code. */
    require_once ( WPRSS_INC . 'update.php' );           
    
    /* Load the shortcodes functions file. */
    require_once ( WPRSS_INC . 'shortcodes.php' );

    /* Load the custom post types and taxonomies. */
    require_once ( WPRSS_INC . 'custom-post-types.php' );  

    /* Load the feed processing functions file */
    require_once ( WPRSS_INC . 'feed-processing.php' );   

    /* Load the feed display functions file */
    require_once ( WPRSS_INC . 'feed-display.php' );            

    /* Load the cron job scheduling functions. */
    require_once ( WPRSS_INC . 'cron-jobs.php' ); 

    /* Load the admin functions file. */
    require_once ( WPRSS_INC . 'admin.php' );         

    /* Load the admin options functions file. */
    require_once ( WPRSS_INC . 'admin-options.php' );             

    /* Load the settings import/export file */
    require_once ( WPRSS_INC . 'admin-import-export.php' ); 

    /* Load the debugging file */
    require_once ( WPRSS_INC . 'system-info.php' ); 

    /* Load the miscellaneous functions file */
    require_once ( WPRSS_INC . 'misc-functions.php' ); 

    /* Load the OPMLL importer file */
    require_once ( WPRSS_INC . 'OPML.php' );       

    /* Load the system info file */
    require_once ( WPRSS_INC . 'admin-debugging.php' );     

    /* Load the admin display-related functions */
    require_once ( WPRSS_INC . 'admin-display.php' );     

    /* Load the admin metaboxes functions */
    require_once ( WPRSS_INC . 'admin-metaboxes.php' );     

    /* Load the scripts loading functions file */
    require_once ( WPRSS_INC . 'scripts.php' );   

    /* Load the Ajax notification file */
    require_once ( WPRSS_INC . 'admin-ajax-notice.php' ); 

    /* Load the logging class */
    require_once ( WPRSS_INC . 'libraries/WP_Logging.php' );   
    
    register_activation_hook( __FILE__ , 'wprss_activate' );
    register_deactivation_hook( __FILE__ , 'wprss_deactivate' );


    add_action( 'init', 'wprss_init' );     
    /**
     * Initialise the plugin
     *
     * @since  1.0
     * @return void
     */     
    function wprss_init() {                    
        do_action( 'wprss_init' );          
    }


    /**
     * Plugin activation procedure
     *
     * @since  1.0
     * @return void
     */  
    function wprss_activate() {
        /* Prevents activation of plugin if compatible version of WordPress not found */
        if ( version_compare( get_bloginfo( 'version' ), '3.3', '<' ) ) {
            deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin
            wp_die( __( 'This plugin requires WordPress version 3.3 or higher.' ), 'WP RSS Aggregator', array( 'back_link' => true ) );
        }  
        wprss_settings_initialize();
        flush_rewrite_rules();
        wprss_schedule_fetch_all_feeds_cron();   
    }    


    /**
     * Plugin deactivation procedure
     *
     * @since 1.0
     */           
    function wprss_deactivate() {
        // on deactivation remove the cron job 
        if ( wp_next_scheduled( 'wprss_fetch_all_feeds_hook' ) ) {
            wp_clear_scheduled_hook( 'wprss_fetch_all_feeds_hook' );
        }
        flush_rewrite_rules();
    }


    add_action( 'plugins_loaded', 'wprss_load_textdomain' );
    /**
     * Loads the plugin's translated strings.
     * 
     * @since  2.1
     * @return void     
     */  
    function wprss_load_textdomain() { 
        load_plugin_textdomain( 'wprss', false, plugin_dir_path( __FILE__ ) . '/languages/' );
    }