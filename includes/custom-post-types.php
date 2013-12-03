<?php    
    /** 
     * Contains all custom post type related functions
     *         
     * @package WPRSSAggregator
     */
    

    add_action( 'init', 'wprss_register_post_types' );
    /**
     * Create Custom Post Types wprss_feed and wprss_feed_item
     * 
     * @since 2.0
     */                 
    function wprss_register_post_types() {        
        
        // Set up labels for the 'wprss_feed' post type
        $labels = apply_filters( 
            'wprss_feed_post_type_labels', 
            array(
                'name'                  => __( 'Feed Sources', 'wprss' ),
                'singular_name'         => __( 'Feed', 'wprss' ),
                'add_new'               => __( 'Add New', 'wprss' ),
                'all_items'             => __( 'All Feed Sources', 'wprss' ),
                'add_new_item'          => __( 'Add New Feed Source', 'wprss' ),
                'edit_item'             => __( 'Edit Feed Source', 'wprss' ),
                'new_item'              => __( 'New Feed Source', 'wprss' ),
                'view_item'             => __( 'View Feed Source', 'wprss' ),
                'search_items'          => __( 'Search Feeds', 'wprss' ),
                'not_found'             => __( 'No Feed Sources Found', 'wprss' ),
                'not_found_in_trash'    => __( 'No Feed Sources Found In Trash', 'wprss' ),
                'menu_name'             => __( 'RSS Aggregator', 'wprss' )
            )
        );

        // Set up the arguments for the 'wprss_feed' post type
        $feed_args = apply_filters( 
            'wprss_feed_post_type_args', 
            array(
                'exclude_from_search'   => true,
                'publicly_querable'     => false,
                'show_in_nav_menus'     => false,
                'show_in_admin_bar'     => true,
                'public'                => true,
                'show_ui'               => true,
                'query_var'             => 'feed_source',
                'menu_position'         => 100,
                'show_in_menu'          => true,
                'show_in_admin_bar'     => true,
                'rewrite'               => array(
                    'slug'       => 'feeds',
                    'with_front' => false
                ),
                'capability_type'       => 'feed',
                'supports'              => array( 'title' ),
                'labels'                => $labels   
            )
        );
        
        if ( version_compare( get_bloginfo( 'version' ), '3.8', '<' ) ) {
            $feed_args['menu_icon'] = WPRSS_IMG . 'icon-adminmenu16-sprite.png';
        }

        // Register the 'wprss_feed' post type
        register_post_type( 'wprss_feed', $feed_args );

        // Set up labels for the 'wprss_feed_item' post type
        $labels = apply_filters( 
            'wprss_feed_item_post_type_labels', 
            array(
                'name'                  => __( 'Imported Feeds', 'wprss' ),
                'singular_name'         => __( 'Imported Feed', 'wprss' ),
                'all_items'             => __( 'Imported Feeds', 'wprss' ),
                'view_item'             => __( 'View Imported Feed', 'wprss' ),                            
                'search_items'          => __( 'Search Imported Feeds', 'wprss' ),
                'not_found'             => __( 'No Imported Feeds Found', 'wprss' ),
                'not_found_in_trash'    => __( 'No Imported Feeds Found In Trash', 'wprss' )
            )
        );

        // Set up the arguments for the 'wprss_feed_item' post type
        $feed_item_args = apply_filters( 
            'wprss_feed_item_post_type_args', 
            array(
                'exclude_from_search'   => true,
                'publicly_querable'     => false,  
                'show_in_nav_menus'     => false,
                'show_in_admin_bar'     => true,
                'public'                => true,
                'show_ui'               => true,
                'query_var'             => 'feed_item',
                'show_in_menu'          => 'edit.php?post_type=wprss_feed',
                'show_in_admin_bar'     => false,
                'rewrite'               => array(
                    'slug'       => 'feeds/items',
                    'with_front' => false,
                ),
                'capability_type'       => 'feed_source',
                'labels'                => $labels
            )
        );

        // Register the 'feed_item' post type
        register_post_type( 'wprss_feed_item', $feed_item_args );        
    }


    /**
     * Filter the link query arguments to exclude the feed and feed item post types. 
     * This filter will only work for WordPress versions 3.7 or higher.
     * 
     * @since 3.4.3
     * @param array $query An array of WP_Query arguments. 
     * @return array $query
     */
    function wprss_modify_link_builder_query( $query ){

        // custom post type slug to be removed
        $to_remove = array( 'wprss_feed', 'wprss_feed_item' );

        // find and remove the array keys
        foreach( $to_remove as $post_type ) {
            $key = array_search( $post_type, $query['post_type'] );
            // remove the array item
            if( $key ) unset( $query['post_type'][$key] );
        }

        return $query; 
    }
    add_filter( 'wp_link_query_args', 'wprss_modify_link_builder_query' );