<?php
    /**
     * Function to create a custom feed with the latest imported feed items
     * 
     * @package WP RSS Aggregator
     * @todo needs implementation, not currently used
     */ 


    add_filter( 'init', 'wprss_addfeed_add_feed' );
    /**
     * Adds feed named 'wprss'
     * 
     * @since 3.0
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
     * Echo the feed
     * 
     * @since 3.0
     */
    function wprss_addfeed_do_feed( $in ) {

        // Prepate the post query
        $query = array( 
            'post_type' => 'wprss_feed_item', 
            'post_status' => 'publish' 
        );

        // Apply filters to the query
        $query = apply_filters( 'wprss_addfeed_do_feed_query', $query );

        // Submit the query to get latest feed items
        query_posts( $query );

        // Send content header and start ATOM output
        header('Content-Type: application/atom+xml');
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