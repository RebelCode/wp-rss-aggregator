<?php
    /**
     * Build the Add-ons page (Code borrowed from the ACF plugin)
     * 
     * @since 4.2
     * @link http://www.advancedcustomfields.com/
     * 
     */ 
    function wprss_addons_page_display() {        

        $premium = wprss_addons_get_extra();
        
        ?>
        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>

            <h2><?php _e( 'Add-Ons', WPRSS_TEXT_DOMAIN ); ?></h2>
            <p><?php _e( "The following Add-ons are available to increase the functionality of the WP RSS Aggregator plugin.", WPRSS_TEXT_DOMAIN ); ?><br />
               <?php _e( "Each Add-on can be installed as a separate plugin.", WPRSS_TEXT_DOMAIN ); ?></p>
        
            <div id="add-ons" class="clearfix">
                
                <div class="add-on-group clearfix">
                <?php foreach( $premium as $_code => $addon ): ?>
                    <?php $isActive = is_plugin_active($addon['basename']) ?>
                    <?php $isInstalledInactive = wprss_is_plugin_inactive($addon['basename']) ?>
                <div class="add-on wp-box<?php if( $isActive ): ?> add-on-active<?php endif; ?> <?php echo sprintf('add-on-code-%1$s', $_code) ?>">
                   <!--  <a target="_blank" href="<?php echo $addon['url']; ?>">
                        <img src="<?php echo $addon['thumbnail']; ?>" />
                    </a> -->
                    <div class="inner">
                        <h3><a target="_blank" href="<?php echo $addon['url']; ?>"><?php echo $addon['title']; ?></a></h3>
                        <p><?php echo $addon['description']; ?></p>
                    </div>
                    <div class="footer">
                        <?php if( $isActive ): ?>
                            <a class="button button-disabled"><span class="wprss-sprite-tick"></span><?php _e( "Installed", WPRSS_TEXT_DOMAIN ); ?></a>
                        <?php elseif( $isInstalledInactive ): ?>
                            <a class="button" href="<?php echo wp_nonce_url('plugins.php?action=activate&amp;plugin='.$addon['basename'], 'activate-plugin_'.$addon['basename'] ) ?>"><?php _e( "Activate", WPRSS_TEXT_DOMAIN ); ?></a>
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

    function wprss_addons_get_extra()
    {
        return apply_filters('wprss_extra_addons', array(
            'et'                    => array(
                'title'                 => 'Excerpts & Thumbnails',
                'description'           => __("Adds the ability to display thumbnails and excerpts. Perfect for adding some life and color to your feed item display. For more flexibility Feed to Post is a better option.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-excerpts-thumbnails/wp-rss-excerpts-thumbnails.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/excerpts-thumbnails/'
            ),
            'c'                     => array(
                'title'                 => 'Categories',
                'description'           => __("Assign categories to your feed sources. Then display a particular category or multiple categories on a post or page via shortcodes.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-categories/wp-rss-categories.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/categories/'
            ),
            'kf'                    => array(
                'title'                 => 'Keyword Filtering',
                'description'           => __("Import feeds that contain specific keywords in either the title or their content. Control what gets imported to your blog. You can use keywords, keyphrases and categories.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-keyword-filtering/wp-rss-keyword-filtering.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/keyword-filtering/'
            ),
            'ftp'                   => array(
                'title'                 => 'Feed to Post',
                'description'           => __("Allows you to import feed items into posts or any other custom post type that you have created. Takes WP RSS Aggregator to a whole new level of flexibility.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-feed-to-post/wp-rss-feed-to-post.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/feed-to-post/'
            ),
            'ftr'                   => array(
                'title'                 => 'Full Text RSS Feeds',
                'description'           => __("This add-ons provides the connectivity to our Full Text Premium service, which gives you unlimited feed items returned per feed source.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-full-text-feeds/wp-rss-full-text.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/full-text-rss-feeds/'
            ),
            'wai'                   => array(
                'title'                 => 'WordAi',
                'description'           => __("Allows you to spin the content for posts imported by Feed to Post using WordAi. Cleverly rewrite your posts without changing their meaning and maintaining human readability.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-wordai/wp-rss-wordai.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/wordai/'
            ),
            'widget'                => array(
                'title'                 => 'Widget',
                'description'           => __("An add-on for WP RSS Aggregator that displays your imported feed items in a widget on your site. Intergrates well with Excerpts &amp; Thumbnails", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-widget/wp-rss-widget.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/widget/'
            ),
            'spc'                   => array(
                'title'                 => 'SpinnerChief',
                'description'           => __("An extension for Feed to Post that allows you to integrate the SpinnerChief article spinner so that the imported content is both completely unique and completely readable.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-spinnerchief/wp-rss-spinnerchief.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/spinnerchief/'
            )
        ));
    }
    
    /**
     * Check if plugin file exists but plugin is inactive
     * @param $path Path to plugin file
     * @since 4.7.3
     * @return bool TRUE if plugin file found but plugin inactive. False otherwise
     */
    function wprss_is_plugin_inactive( $path ){
        
        if( ! isset( $path ) ){
            return FALSE;
        }
        
        if( file_exists( WP_PLUGIN_DIR . '/' . $path ) && is_plugin_inactive( $path ) ){
            return TRUE; // plugin found but inactive
        }
        
        return FALSE;
        
    }