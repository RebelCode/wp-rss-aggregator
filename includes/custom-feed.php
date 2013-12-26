<?php
    /**
     * Function to create a custom feed with the latest imported feed items
     * 
     * @package WP RSS Aggregator
     */ 


    add_action( 'init', 'wprss_addfeed_add_feed' );
    /**
     * Adds the custom feed, as specified by the user in the general settings.
     * 
     * @since 3.3
     */
    function wprss_addfeed_add_feed() {
        $general_settings = get_option( 'wprss_settings_general', 'wprss' );
        if ( !empty( $general_settings ) && isset( $general_settings['custom_feed_url'] ) && !empty( $general_settings['custom_feed_url'] ) ) {
            $url = $general_settings['custom_feed_url'];
        }
        else {
            $url = $general_settings['custom_feed_url'] = 'wprss';
            update_option( 'wprss_settings_general', $general_settings );
        }

        // Add the feed
        add_feed( $url, 'wprss_addfeed_do_feed' );

        // Whether or not the feed is already registered or not
        $registered = FALSE;
        
        // Get all registered rewrite rules
        $rules = get_option( 'rewrite_rules' );

        // If no rules exist, then it is not registered
        if ( !is_array( $rules ) ) {
            $registered = FALSE;
        }
        // If there are exisiting rules
        else {
            // Get all the array keys that match the given pattern
            // The resulting array will only contain the second part of each matching key ( $matches[1] )
            $feeds = array_keys( $rules, 'index.php?&feed=$matches[1]' );
            // Check if the rewrite rule for the custom feed is already registered
            foreach( $feeds as $feed ) {
                if ( strpos( $feed, $url ) !== FALSE ) {
                    $registered = TRUE;
                }
            }
        }

        // If not registered, flush the rewrite rules
        if ( ! $registered ) {
            flush_rewrite_rules();
        }

    }


    /**
     * Generate the feed
     * 
     * @since 3.3
     */
    function wprss_addfeed_do_feed( $in ) {

        // Prepare the post query
        /*
        $wprss_custom_feed_query = apply_filters(            
                'wprss_custom_feed_query',
                array(
                'post_type'   => 'wprss_feed_item', 
                'post_status' => 'publish',
                'cache_results' => false,   // disable caching
            ) 
            
        );*/
        $wprss_custom_feed_query = wprss_get_feed_items_query(
            apply_filters(
                'wprss_custom_feed_query',
                array(
                    'get-args'      =>  TRUE, // Get the query args instead of the query object
                    'no-paged'      =>  TRUE, // ignore pagination
                    'feed_limit'    =>  0, // ignore limit
                )
            )
        );

        // Suppress caching
        $wprss_custom_feed_query['cache_results'] = FALSE;

        // Get options
        $options = get_option( 'wprss_settings_general' );
        if ( $options !== FALSE ) {
            // If options exist, get the limit
            $limit = $options['custom_feed_limit'];
            if ( $limit !== FALSE ) {
                // if limit exists, set the query limit
                $wprss_custom_feed_query['posts_per_page'] = $limit;
            }
        }

        // Submit the query to get latest feed items
        query_posts( $wprss_custom_feed_query );

        // Send content header and start ATOM output
        header('Content-Type: text/xml');
        // Disabling caching
        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
        header('Pragma: no-cache'); // HTTP 1.0.
        header('Expires: 0'); // Proxies.
        echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';
        ?>
        <feed xmlns="http://www.w3.org/2005/Atom">
            <title type="text">Latest imported feed items on <?php bloginfo_rss('name'); ?></title>
            <?php
            // Start the Loop
            while ( have_posts() ) : the_post();
            $permalink = get_post_meta( get_the_ID(), 'wprss_item_permalink', true );
            ?>
            <entry>
                <title><![CDATA[<?php the_title_rss(); ?>]]></title>
                <link href="<?php echo $permalink; ?>" />
                <?php // Enable below to link to post on our site rather than original source ?>
                <!--<link href="<?php the_permalink_rss(); ?>" />-->
                <published><?php echo get_post_time( 'Y-m-d\TH:i:s\Z' ); ?></published>
                <content type="html"><![CDATA[<?php the_content(); ?>]]></content>
            </entry>
            <?php
            // End of the Loop
            endwhile;
            ?>
        </feed>
        <?php
    }    


    add_filter( 'post_limits', 'wprss_custom_feed_limits' );
    /**
     * Set a different limit to our custom feeds
     * 
     * @since 3.3
     */
    function wprss_custom_feed_limits( $limit ) {
        if ( is_feed( ) ) { 
        //    return 'LIMIT 0, 3';  
        return $limit; 
        }
        return $limit;
    }