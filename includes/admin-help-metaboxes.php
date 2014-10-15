<?php

if( class_exists('WPRSS_Help') ) {
    $help = WPRSS_Help::get_instance();

    // Feed source setting fields
    $prefix = 'field_';
    $tooltips = array(
        /* -----------------------------
         *  Feed Source Details Metabox
         * -----------------------------
         */
            // Feed Source URL
            'wprss_url'             => 'The URL of the feed source. In most cases, the URL of the site will also work, but for best results we recommend trying to find the URL of the RSS feed.

                                        Also include the <code>http://</code> prefix in the URL.',
            // Feed limit
            'wprss_limit'           => 'The maximum number of imported items from this feed to keep stored.

                                        When new items are imported and the limit is exceeded, the oldest feed items will be deleted to make room for new ones.

                                        If you already have items imported from this feed source, setting this option now may delete some of your items, in order to comply with the limit.',
            // Link to Enclosure
            'wprss_enclosure'       => 'Tick this box to make feed items link to the URL in the enclosure tag, rather than link to the original article.

                                        Enclosure tags are RSS tags that may be included with a feed items. These tags typically contain links to images, audio, videos, attachment files or even flash content.

                                        If you are not sure leave this setting blank.',

        /* -------------------------
         *  Feed Processing Metabox
         * -------------------------
         */
            // Feed State
            'wprss_state'           => 'State of the feed, active or paused.

                                        If active, the feed source will fetch items periodically, according to the settings below.

                                        If paused, the feed source will not fetch feed items periodically.',
            // Activate Feed: [date]
            'wprss_activate_feed'   => 'You can set a time, in UTC, in the future when the feed source will become active, if it is paused.

                                        Leave blank to activate immediately.',
            // Pause Feed: [date]
            'wprss_pause_feed'      => 'You can set a time, in UTC, in the future when the feed source will become paused, if it is active.

                                        Leave blank to never pause.',
            // Update Interval
            'wprss_update_interval' => 'How frequently the feed source should check for new items and fetch if needed.

                                        If left as <em>Default</em>, the interval in the global settings is used.',
            // Delete items older than: [date]
            'wprss_age_limit'       => 'The maximum age allowed for feed items. Very useful if you are only concerned with, say, last week\'s news.

                                        Items already imported will be deleted if they eventually exceed this age limit.

                                        Also, items in the RSS feed that are already older than this age will not be imported at all.

                                        Leaving empty to use the <em>Limit feed items by age</em> option in the general settings.',

        /* ----------------------
         *  Feed Preview Metabox
         * ----------------------
         */
        // Force Feed
        'wprss_force_feed'      => 'Use this option if you are seeing an <q>Invalid feed URL</q> error in the Feed Preview above, but you are sure that the URL is correct.

                                    Note, however, that this will disable auto-discovery, meaning that the given URL must be an RSS feed URL. Using the site\'s URL will not work.'
    );
    $help->add_tooltips( $tooltips, $prefix );
}