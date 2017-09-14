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

            <h2><?php _e( 'More Features With Our Premium Add-Ons', WPRSS_TEXT_DOMAIN ); ?></h2>
            <p><?php echo sprintf(__( 'The following <a href="%1$s" target="_blank">add-ons</a> are available to increase the functionality of the WP RSS Aggregator plugin.', WPRSS_TEXT_DOMAIN ), 'http://www.wprssaggregator.com/extensions') ?></p>
            <p><?php echo sprintf(__(
                       'Save more with our <a href="%1$s" target="_blank"><strong>Simple Feeds Bundle</strong></a> or'
                       . ' <a href="%2$s" target="_blank"><strong>Advanced Feeds Bundle</strong></a>!', WPRSS_TEXT_DOMAIN ),
                       'https://www.wprssaggregator.com/extension/simple-feeds-bundle/',
                       'https://www.wprssaggregator.com/extension/advanced-feeds-bundle/'); ?></p>
        
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
            'ftp'                   => array(
                'title'                 => 'Feed to Post',
                'description'           => __("An advanced importer that lets you import RSS feed items as WordPress posts or any other custom post type. You can use it to populate a website in minutes (auto-blog). This is the most popular and feature-filled extension.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-feed-to-post/wp-rss-feed-to-post.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/feed-to-post/'
            ),
            'ftr'                   => array(
                'title'                 => 'Full Text RSS Feeds',
                'description'           => __("Adds connectivity to our premium full text service, which allows you to import the full post content for an unlimited number of feed items per feed source, even when the feed itself doesn't provide it", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-full-text-feeds/wp-rss-full-text.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/full-text-rss-feeds/'
            ),
            'wai'                   => array(
                'title'                 => 'WordAi',
                'description'           => __("An extension for Feed to Post that allows you to integrate the WordAi article spinner so that the imported content is both completely unique and completely readable.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-wordai/wp-rss-wordai.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/wordai/'
            ),
            'spc'                   => array(
                'title'                 => 'SpinnerChief',
                'description'           => __("An extension for Feed to Post that allows you to integrate the SpinnerChief article spinner so that the imported content is both completely unique and completely readable.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-spinnerchief/wp-rss-spinnerchief.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/spinnerchief/'
            ),
            'et'                    => array(
                'title'                 => 'Excerpts & Thumbnails',
                'description'           => __("Displays an excerpt and thumbnail image (taken from within the RSS feed) together with the title, date and source of each feed item. It uses the shortcode to display the feed items.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-excerpts-thumbnails/wp-rss-excerpts-thumbnails.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/excerpts-thumbnails/'
            ),
            'c'                     => array(
                'title'                 => 'Categories',
                'description'           => __("Categorises your feed sources and allows you to display feed items from a particular category within your site using the shortcode parameters.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-categories/wp-rss-categories.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/categories/'
            ),
            'kf'                    => array(
                'title'                 => 'Keyword Filtering',
                'description'           => __("Filters the feed items to be imported based on your own keywords, key phrases, or tags; you only get the items you're interested in. It is compatible with all other add-ons.", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-keyword-filtering/wp-rss-keyword-filtering.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/keyword-filtering/'
            ),
            'widget'                => array(
                'title'                 => 'Widget',
                'description'           => __("Adds a widget to your website that displays all the imported feed items. It can also display excerpts and thumbnail images when used in conjunction with the Excerpts &amp; Thumbnails add-on", WPRSS_TEXT_DOMAIN),
                'thumbnail'             => WPRSS_IMG . 'add-ons/wprss.jpg',
                'basename'              => 'wp-rss-widget/wp-rss-widget.php',
                'url'                   => 'http://www.wprssaggregator.com/extension/widget/'
            ),
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