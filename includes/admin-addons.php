<?php
    /**
     * Build the Add-ons page (Code borrowed from the ACF plugin)
     * 
     * @since 4.2
     * @link http://www.advancedcustomfields.com/
     * 
     */ 
    function wprss_addons_page_display() {        
        
        $premium = array();
        $premium[] = array(
            'title' => __( "Excerpts & Thumbnails", WPRSS_TEXT_DOMAIN ),
            'description' => __( "Adds the ability to display thumbnails and excerpts. Perfect for adding some life and color to your feed item display. For more flexibility Feed to Post is a better option.", WPRSS_TEXT_DOMAIN ),
            'thumbnail' => WPRSS_IMG . 'add-ons/wprss.jpg',
            'active' => is_plugin_active( 'wp-rss-excerpts-thumbnails/wp-rss-excerpts-thumbnails.php' ),
            'url' => 'http://www.wprssaggregator.com/extension/excerpts-thumbnails/'
        );
        $premium[] = array(
            'title' => __( "Categories", WPRSS_TEXT_DOMAIN ),
            'description' => __( "Assign categories to your feed sources. Then display a particular category or multiple categories on a post or page via shortcodes.", WPRSS_TEXT_DOMAIN ),
            'thumbnail' => WPRSS_IMG . 'add-ons/wprss.jpg',
            'active' => is_plugin_active( 'wp-rss-categories/wp-rss-categories.php' ),
            'url' => 'http://www.wprssaggregator.com/extension/categories/'
        );
        $premium[] = array(
            'title' => __( "Keyword Filtering", WPRSS_TEXT_DOMAIN ),
            'description' => __( "Import feeds that contain specific keywords in either the title or their content. Control what gets imported to your blog. You can use keywords, keyphrases and categories.", WPRSS_TEXT_DOMAIN ),
            'thumbnail' => WPRSS_IMG . 'add-ons/wprss.jpg',
            'active' => is_plugin_active( 'wp-rss-keyword-filtering/wp-rss-keyword-filtering.php' ),
            'url' => 'http://www.wprssaggregator.com/extension/keyword-filtering/'
        );
        $premium[] = array(
            'title' => __( "Feed to Post", WPRSS_TEXT_DOMAIN ),
            'description' => __( "Allows you to import feed items into posts or any other custom post type that you have created. Takes WP RSS Aggregator to a whole new level of flexibility.", WPRSS_TEXT_DOMAIN ),
            'thumbnail' => WPRSS_IMG . 'add-ons/wprss.jpg',
            'active' => is_plugin_active( 'wp-rss-feed-to-post/wp-rss-feed-to-post.php' ),
            'url' => 'http://www.wprssaggregator.com/extension/feed-to-post/'
        );
        $premium[] = array(
            'title' => __( "Full Text RSS Feeds", WPRSS_TEXT_DOMAIN ),
            'description' => __( "This add-ons provides the connectivity to our Full Text Premium service, which gives you unlimited feed items returned per feed source.", WPRSS_TEXT_DOMAIN ),
            'thumbnail' => WPRSS_IMG . 'add-ons/wprss.jpg',
            'active' => is_plugin_active( 'wp-rss-full-text-feeds/wp-rss-full-text.php' ),
            'url' => 'http://www.wprssaggregator.com/extension/full-text-rss-feeds/'
        );        
        $premium[] = array(
            'title' => __( "WordAi", WPRSS_TEXT_DOMAIN ),
            'description' => __( "Allows you to spin the content for posts imported by Feed to Post using WordAi. Cleverly rewrite your posts without changing their meaning and maintaining human readability.", WPRSS_TEXT_DOMAIN ),
            'thumbnail' => WPRSS_IMG . 'add-ons/wprss.jpg',
            'active' => is_plugin_active( 'wp-rss-wordai/wp-rss-wordai.php' ),
            'url' => 'http://www.wprssaggregator.com/extension/wordai/'
        );
        
        ?>
        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>

            <h2><?php _e( 'Add-Ons', WPRSS_TEXT_DOMAIN ); ?></h2>
            <p><?php _e( "The following Add-ons are available to increase the functionality of the WP RSS Aggregator plugin.", WPRSS_TEXT_DOMAIN ); ?><br />
               <?php _e( "Each Add-on can be installed as a separate plugin. Note that activating the Feed to Post plugin will deactivate the Categories and Excerpts & Thumbnails add-ons.", WPRSS_TEXT_DOMAIN ); ?></p>        
        
            <div id="add-ons" class="clearfix">
                
                <div class="add-on-group clearfix">
                <?php foreach( $premium as $addon ): ?>
                <div class="add-on wp-box <?php if( $addon['active'] ): ?>add-on-active<?php endif; ?>">
                   <!--  <a target="_blank" href="<?php echo $addon['url']; ?>">
                        <img src="<?php echo $addon['thumbnail']; ?>" />
                    </a> -->
                    <div class="inner">
                        <h3><a target="_blank" href="<?php echo $addon['url']; ?>"><?php echo $addon['title']; ?></a></h3>
                        <p><?php echo $addon['description']; ?></p>
                    </div>
                    <div class="footer">
                        <?php if( $addon['active'] ): ?>
                            <a class="button button-disabled"><span class="wprss-sprite-tick"></span><?php _e( "Installed", WPRSS_TEXT_DOMAIN ); ?></a>
                        <?php else: ?>
                            <a target="_blank" href="<?php echo $addon['url']; ?>" class="button"><?php _e( "Purchase & Install", WPRSS_TEXT_DOMAIN ); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
                        
            </div>
            
        </div>

                <?php
                        
    }