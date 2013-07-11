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
                'add_new'               => __( 'Add New Feed Source', 'wprss' ),
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
                'menu_icon'             => WPRSS_IMG . 'icon-adminmenu16-sprite.png',
                'show_in_menu'          => true,
                'rewrite'               => array(
                                            'slug'       => 'feeds',
                                            'with_front' => false
                                        ), 
                'capability_type'       => 'feed',
                'map_meta_cap'          => true,
                'supports'              => array( 'title' ),
                'labels'                => $labels   
            )
        );
        
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
                'public'                => false,
                'show_ui'               => true,
                'query_var'             => 'feed_item',
                'show_in_menu'          => 'edit.php?post_type=wprss_feed',
                'rewrite'               => array(
                                            'slug'       => 'feeds/items',
                                            'with_front' => false,
                                        ),       
                'labels'                => $labels
            )
        );
        
        // Register the 'feed_item' post type
        register_post_type( 'wprss_feed_item', $feed_item_args );        
    }