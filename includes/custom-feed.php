<?php
    /**
     * Function to create a custom feed with the latest imported feed items
     * 
     * @package WP RSS Aggregator
     */ 


    add_filter( 'init', 'wprss_addfeed_add_feed' );
    /**
     * Adds feed named 'wprss'
     * 
     * @since 3.3
     */
    function wprss_addfeed_add_feed() {
        $general_settings = get_option( 'wprss_settings_general', 'wprss' );
        if ( !empty( $general_settings ) && isset( $general_settings['custom_feed_url'] ) ) {
            $url = $general_settings['custom_feed_url'];
        }
        else $url = 'wprss';
        add_feed( $url, 'wprss_addfeed_do_feed' );
        flush_rewrite_rules();
    }


    /**
     * Generate the feed
     * 
     * @since 3.3
     */
    function wprss_addfeed_do_feed( $in ) {

        // Prepare the post query
        $wprss_custom_feed_query = apply_filters(            
                'wprss_custom_feed_query',
                array(
                'post_type'   => 'wprss_feed_item', 
                'post_status' => 'publish',
              //   'posts_per_page' => 4,  // works if enabled
                'cache_results' => false,   // disable caching
            ) 
            
        );

        // Submit the query to get latest feed items
        query_posts( $wprss_custom_feed_query );

        // Send content header and start ATOM output
        header('Content-Type: application/atom+xml');
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
            ?>
            <entry>
                <title><![CDATA[<?php the_title_rss(); ?>]]></title>
                <link href="<?php the_permalink_rss(); ?>" />
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