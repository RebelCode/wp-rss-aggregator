<?php
    /**
     * Build the Help page
     * 
     * @since 4.2
     */ 
    function wprss_help_page_display() {        
        ?>

        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>

            <h2><?php _e( 'Help & Support', 'wprss' ); ?></h2>
            <h3>Documentation</h3>
            <p>In the <a href="www.wprssaggregator.com/documentation/">documentation area</a> on the WP RSS Aggregator website you will find comprehensive details on how to use the core plugin
            and all the add-ons.</p><p>There are also some videos to help you make a quick start to setting up and enjoying this plugin.</p>
            <h3>Frequently Asked Questions (FAQ)</h3>
            <p>If after going through the documentation you still have questions, please take a look at the <a href="http://www.wprssaggregator.com/faq/">FAQ page</a> on the site, we set this
            up purposely to answer the most commonly asked questions by our users.</p>
            <h3>Support Forums - Core (free version) Plugin Users Only</h3>
            <p>If you're using the free version of the plugin found on WordPress.org, you can ask questions on the <a href="http://wordpress.org/support/plugin/wp-rss-aggregator">support forum</a>.</p>
            <h3>Email Ticketing System - Premium Add-on Users Only</h3>
            <p>If you still can't find an answer to your query after reading the documentation and going through the FAQ, just <a href="http://www.wprssaggregator.com/contact/">open a support request ticket</a>.<br> 
            We'll be happy to help you out.</p>
        </div>
    <?php
    }  