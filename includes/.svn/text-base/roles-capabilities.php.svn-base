<?php
    
    /** 
     * Contains all roles and capabilities functions
     *         
     * @package WPRSSAggregator
     */


    add_action('init', 'wprss_add_caps');
    /**
     * Add feed aggregator-specific capabilities
     * 
     * @since 3.3
     */
    function wprss_add_caps() {
        global $wp_roles;

        if ( class_exists('WP_Roles') )
            if ( ! isset( $wp_roles ) )
                $wp_roles = new WP_Roles();

        if ( is_object( $wp_roles ) ) {

            $wp_roles->add_cap( 'administrator', 'manage_feed_settings' );
            $wp_roles->add_cap( 'editor', 'manage_feed_settings' );

            // Add the main post type capabilities
            $capabilities = wprss_get_core_caps();
            foreach ( $capabilities as $cap_group ) {
                foreach ( $cap_group as $cap ) {                    
                    $wp_roles->add_cap( 'administrator', $cap );
                    $wp_roles->add_cap( 'editor', $cap );                    
                }
            }
        }
    }    


    /**
     * Gets the core post type capabilties
     * 
     * @since 3.3
     */
    function wprss_get_core_caps() {
        $capabilities = array();

        $capability_types = array( 'feed', 'feed_source' );

        foreach ( $capability_types as $capability_type ) {
            $capabilities[ $capability_type ] = array(
                // Post type
                "edit_{$capability_type}",
                "read_{$capability_type}",
                "delete_{$capability_type}",
                "edit_{$capability_type}s",
                "edit_others_{$capability_type}s",
                "publish_{$capability_type}s",
                "read_private_{$capability_type}s",
                "delete_{$capability_type}s",
                "delete_private_{$capability_type}s",
                "delete_published_{$capability_type}s",
                "delete_others_{$capability_type}s",
                "edit_private_{$capability_type}s",
                "edit_published_{$capability_type}s",

                // Terms
                "manage_{$capability_type}_terms",
                "edit_{$capability_type}_terms",
                "delete_{$capability_type}_terms",
                "assign_{$capability_type}_terms"
            );
        }

        return $capabilities;
    }


    /**
     * Remove core post type capabilities (called on uninstall)
     *
     * @since 3.3
     * @return void
     */
    function wprss_remove_caps() {
        if ( class_exists( 'WP_Roles' ) )
            if ( ! isset( $wp_roles ) )
                $wp_roles = new WP_Roles();

        if ( is_object( $wp_roles ) ) {

            /** Site Administrator Capabilities */
            $wp_roles->remove_cap( 'administrator', 'manage_feed_settings' );
            /** Editor Capabilities */
            $wp_roles->remove_cap( 'editor', 'manage_feed_settings' );

            /** Remove the Main Post Type Capabilities */
            $capabilities = $this->get_core_caps();

            foreach ( $capabilities as $cap_group ) {
                foreach ( $cap_group as $cap ) {                    
                    $wp_roles->remove_cap( 'administrator', $cap );
                    $wp_roles->remove_cap( 'editor', $cap );
                }
            }
        }
    }