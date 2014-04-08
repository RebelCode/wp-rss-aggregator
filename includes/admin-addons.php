<?php
    /**
     * Build the Add-ons page
     * 
     * @since 4.2
     * @link http://www.advancedcustomfields.com/
     * 
     */ 
    function wprss_addons_page_display() {        
        
        $premium = array();
        $premium[] = array(
            'title' => __( "Excerpts & Thumbnails", 'wprss' ),
            'description' => __( "Adds the ability to display thumbnails and excerpts. Perfect for adding some life and color to your feed item display. For more flexibility Feed to Post is a better option.", 'wprss' ),
            'thumbnail' => WPRSS_IMG .'add-ons/wprss.jpg',
            'active' => class_exists( 'acf_field_repeater' ),
            'url' => 'http://www.wprssaggregator.com/extensions/excerpts-thumbnails/'
        );
        $premium[] = array(
            'title' => __( "Categories", 'wprss' ),
            'description' => __( "Assign categories to your feed sources. Then display a particular category or multiple categories on a post or page via shortcodes.", 'wprss' ),
            'thumbnail' => WPRSS_IMG .'add-ons/wprss.jpg',
            'active' => class_exists( 'acf_field_repeater' ),
            'url' => 'http://www.wprssaggregator.com/extensions/categories/'
        );
        $premium[] = array(
            'title' => __( "Keyword Filtering", 'wprss' ),
            'description' => __( "Import feeds that contain specific keywords in either the title or their content. Control what gets imported to your blog. You can use keywords, keyphrases and categories.", 'wprss' ),
            'thumbnail' => WPRSS_IMG .'add-ons/wprss.jpg',
            'active' => class_exists( 'acf_field_repeater' ),
            'url' => 'http://www.wprssaggregator.com/extensions/keyword-filtering/'
        );
        $premium[] = array(
            'title' => __( "Feed to Post", 'wprss' ),
            'description' => __( "Allows you to import feed items into posts or any other custom post type that you have created. Takes WP RSS Aggregator to a whole new level of flexibility.", 'wprss' ),
            'thumbnail' => WPRSS_IMG .'add-ons/wprss.jpg',
            'active' => class_exists( 'acf_field_repeater' ),
            'url' => 'http://www.wprssaggregator.com/extensions/feed-to-post/'
        );
        /*
        
        $free = array();
        $free[] = array(
            'title' => __("Gravity Forms Field",'acf'),
            'description' => __("Creates a select field populated with Gravity Forms!",'acf'),
            'thumbnail' => $dir . 'images/add-ons/gravity-forms-field-thumb.jpg',
            'active' => class_exists('gravity_forms_field'),
            'url' => 'https://github.com/stormuk/Gravity-Forms-ACF-Field/'
        );
        $free[] = array(
            'title' => __("Date & Time Picker",'acf'),
            'description' => __("jQuery date & time picker",'acf'),
            'thumbnail' => $dir . 'images/add-ons/date-time-field-thumb.jpg',
            'active' => class_exists('acf_field_date_time_picker'),
            'url' => 'http://wordpress.org/extend/plugins/acf-field-date-time-picker/'
        );
        $free[] = array(
            'title' => __("Location Field",'acf'),
            'description' => __("Find addresses and coordinates of a desired location",'acf'),
            'thumbnail' => $dir . 'images/add-ons/google-maps-field-thumb.jpg',
            'active' => class_exists('acf_field_location'),
            'url' => 'https://github.com/elliotcondon/acf-location-field/'
        );
        $free[] = array(
            'title' => __("Contact Form 7 Field",'acf'),
            'description' => __("Assign one or more contact form 7 forms to a post",'acf'),
            'thumbnail' => $dir . 'images/add-ons/cf7-field-thumb.jpg',
            'active' => class_exists('acf_field_cf7'),
            'url' => 'https://github.com/taylormsj/acf-cf7-field/'
        );*/
        
        ?>
        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>

            <h2><?php _e( 'Add-Ons', 'wprss' ); ?></h2>
            <p><?php _e( "The following Add-ons are available to increase the functionality of the WP RSS Aggregator plugin.", 'wprss' ); ?><br />
               <?php _e( "Each Add-on can be installed as a separate plugin. Note that activating the Feed to Post plugin will deactivate the Categories and Excerpts & Thumbnails add-ons.", 'wprss' ); ?></p>
            
            <?php /*
            <div class="acf-alert">
                <p><strong><?php _e("Just updated to version 4?",'acf'); ?></strong> <?php _e("Activation codes have changed to plugins! Download your purchased add-ons",'acf'); ?> <a href="http://www.advancedcustomfields.com/add-ons-download/" target="_blank"><?php _e("here",'acf'); ?></a></p>
            </div>
            */ ?>
        
            <div id="add-ons" class="clearfix">
                
                <div class="add-on-group clearfix">
                <?php foreach( $premium as $addon ): ?>
                <div class="add-on wp-box <?php if( $addon['active'] ): ?>add-on-active<?php endif; ?>">
                    <a target="_blank" href="<?php echo $addon['url']; ?>">
                        <img src="<?php echo $addon['thumbnail']; ?>" />
                    </a>
                    <div class="inner">
                        <h3><a target="_blank" href="<?php echo $addon['url']; ?>"><?php echo $addon['title']; ?></a></h3>
                        <p><?php echo $addon['description']; ?></p>
                    </div>
                    <div class="footer">
                        <?php if( $addon['active'] ): ?>
                            <a class="button button-disabled"><span class="acf-sprite-tick"></span><?php _e("Installed",'acf'); ?></a>
                        <?php else: ?>
                            <a target="_blank" href="<?php echo $addon['url']; ?>" class="button"><?php _e("Purchase & Install",'acf'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
                
               <!-- <div class="add-on-group clearfix">
                <?php foreach( $free as $addon ): ?>
                <div class="add-on wp-box <?php if( $addon['active'] ): ?>add-on-active<?php endif; ?>">
                    <a target="_blank" href="<?php echo $addon['url']; ?>">
                        <img src="<?php echo $addon['thumbnail']; ?>" />
                    </a>
                    <div class="inner">
                        <h3><a target="_blank" href="<?php echo $addon['url']; ?>"><?php echo $addon['title']; ?></a></h3>
                        <p><?php echo $addon['description']; ?></p>
                    </div>
                    <div class="footer">
                        <?php if( $addon['active'] ): ?>
                            <a class="button button-disabled"><span class="acf-sprite-tick"></span><?php _e("Installed",'acf'); ?></a>
                        <?php else: ?>
                            <a target="_blank" href="<?php echo $addon['url']; ?>" class="button"><?php _e("Download",'acf'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>    
                </div>-->
                
                        
            </div>
            
        </div>
        <script type="text/javascript">
        (function($) {
            
            $(window).load(function(){
                
                $('#add-ons .add-on-group').each(function(){
                
                    var $el = $(this),
                        h = 0;
                    
                    
                    $el.find('.add-on').each(function(){
                        
                        h = Math.max( $(this).height(), h );
                        
                    });
                    
                    $el.find('.add-on').height( h );
                    
                });
                
            });
            
        })(jQuery); 
        </script>
                <?php
                
                return;
        
    }



