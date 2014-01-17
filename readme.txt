=== WP RSS Aggregator ===
Contributors: jeangalea
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X9GP6BL4BLXBJ
Plugin URI: http://www.wprssaggregator.com
Tags: rss, feeds, aggregation, autoblog aggregator, rss import, feed aggregator, rss aggregator, multiple rss feeds, multi rss feeds, rss multi importer, feed import, feed import, multiple feed import, feed aggregation, rss feader, feed reader, feed to post, multiple feeds, multi feed importer, multi feed import, multi import, autoblogging, autoblogger
Requires at least: 3.3
Tested up to: 3.8
Stable tag: 3.9.7
License: GPLv2 or later
Imports and aggregates multiple RSS Feeds using SimplePie. Outputs feeds sorted by date (latest first).


== Description ==

WP RSS Aggregator is the most comprehensive and elegant RSS feed solution for WordPress.

The original and premier plugin for importing, merging and displaying RSS and Atom feeds on your WordPress site.

With WP RSS Aggregator, you can:

* Display feeds from one or more sites on your blog 
* Aggregate feeds from multiple sites 

You can add any number of feeds through an administration panel, the plugin will then pull feed items from these sites, merge them and display them in date order.

To display your imported feed items, you can use a shortcode or call the display function directly from within your theme.

__More Features__: 

* Export a custom RSS feed based on your feed sources
* Pagination
* Set the feed import time interval
* Scheduling of feed imports by feed source
* Various shortcode parameters you can use to further customize the output
* Choose whether to show/hide sources and dates
* Choose the date format
* Set the links as no-follow or not, or add no follow to meta tag
* Select how you would like the links to open (in a Lightbox, a new window, or the current window)
* Set the name of the feed source
* Select number of posts per feed you want to show and store
* Opens YouTube, DailyMotion and Vimeo videos directly 
* Limit number of feed items stored in the database
* Feed autodiscovery, which lets you add feeds without even knowing the exact URL. 
* Extendable via action and filter hooks
* Integrated with the Simplepie library that come with WordPress. This includes RSS 0.91 and RSS 1.0 formats, the popular RSS 2.0 format, Atom etc.

= Premium Add-Ons =	
Add-Ons that add more functionality to the core plugin are now [available for purchase](http://www.wprssaggregator.com/extensions/). 

* [Feed to Post](http://www.wprssaggregator.com/extensions/feed-to-post) - an advanced importer that lets you import RSS feeds into posts or custom post types. Populate a website in minutes (autoblog).
* [Keyword Filtering](http://www.wprssaggregator.com/extensions/keyword-filtering) - filter imported feeds based on keywords, so you only get items you're interested in.
* [Excerpts & Thumbnails](http://www.wprssaggregator.com/extensions/excerpts-thumbnails) - display excerpts and thumbnails together with the title, date and source.
* [Categories](http://www.wprssaggregator.com/extensions/categories) - categorise your feed sources and display items from a particular category at will within your site.

= Demo =
The plugin can be seen in use on the [demo page](http://www.wprssaggregator.com/demo/).

= Video Walkthrough =
[youtube http://www.youtube.com/watch?v=5J-S2vXtQ5w]

= Documentation =
Instructions for plugin usage are available on the plugin's [documentation page](http://www.wprssaggregator.com/documentation/).

= Credit = 
Created by Jean Galea from [WP Mayor](http://www.wpmayor.com)

= Technical Stuff =
WP RSS Aggregator uses the SimplePie class to import and handle feeds, and stores all feed sources and feed items as custom post types in the WordPress default table structure, thus no custom tables are added. 

= Translations =
Italian - Davide De Maestri


== Installation ==

1. Upload the `wp-rss-aggregator` folder to the `/wp-content/plugins/` directory
2. Activate the WP RSS Aggregator plugin through the 'Plugins' menu in WordPress
3. Configure the plugin by going to the `RSS Aggregator` menu item that appears in your dashboard menu.
3. Use the shortcode in your posts or pages: `[wp-rss-aggregator]`

The parameters accepted are:

* links_before
* links_after
* link_before
* link_after
* limit
* source

An example of a shortcode with parameters:
`[wp_rss_aggregator link_before='<li class="feed-link">' link_after='</li>']`
It is advisable to use the 'HTML' view of the editor when inserting the shortcode with paramters.

__Usage within theme files__

An example of a function call from within the theme's files:
`
<?php 
wprss_display_feed_items( $args = array(
	'links_before' => '<ul>',
	'links_after' => '</ul>',
	'link_before' => '<li>',
	'link_after' => '</li>',
	'limit' => '8',
	'source' => '5,9'
	)); 
?>
`

OR 

`<?php do_shortcode('[wp-rss-aggregator]'); ?>`


== Frequently Asked Questions ==
= How can I output the feeds in my theme? =

You can either call the function directly within the theme:
`<?php wprss_display_feed_items(); ?>`

Or use the shortcode in your posts and pages:
[wp-rss-aggregator]

= Can I store imported feed items as posts? = 

You can do that with the [Feed to Post](http://www.wprssaggregator.com/extensions/feed-to-post) add-on. You will not only be able to store items as posts, but also as other custom post types, as well as set the author, auto set tags and categories, and much more. 

= Some RSS feeds only give a short excerpt, any way around that? =

Yes, within the [Feed to Post](http://www.wprssaggregator.com/extensions/feed-to-post) add-on we have an advanced feature that can get the full content of those feeds that only supply a short excerpt.


== Screenshots ==

1. The output of this plugin on the frontend.

2. The output from the aggregator with the [Excerpts & Thumbnails](http://www.wprssaggregator.com/extensions/excerpts-thumbnails) add-on installed.

3. Adding a new feed source.

4. Imported feeds.

5. Plugin settings page.


== Changelog ==

= 3.9.7 (2014-01-17) =
* Fixed bug: Bug in admin-debugging.php causing trouble with admin login

= 3.9.6 (2014-01-17) =
* Enhanced: Added error logging.

= 3.9.5 (2014-01-02) =
* Enhanced: Added a feed validator link in the New/Edit Feed Sources page.
* Enhanced: The Next Update column also shows the time remaining for next update, for feed source on the global update interval.
* Enhanced: The custom feed has been improved, and is now identical to the feeds displayed with the shortcode.
* Enhanced: License notifications only appear on the main site when using WordPress multisite.
* Enhanced: Updated Colorbox script to 1.4.33
* Fixed bug: The Imported Items column was always showing zero.
* Fixed bug: Feed items not being imported with limit set to zero. Should be unlimited.
* Fixed bug: Fat header in Feed Sources page

= 3.9.4 (2013-12-24) =
* Enhanced: Added a column in the Feed Sources page that shows the number of feed items imported for each feed source.
* Fixed bug: Leaving the delete old feed items empty did not ignore the delete.

= 3.9.3 (2013-12-23) =
* Fixed bug: Fixed tracking pointer appearing on saving settings.

= 3.9.2 (2013-12-21) = 
* Fixed bug: Incorrect file include call.

= 3.9.1 (2013-12-12) =
* Enhanced: Improved date and time handling for imported feed items.
* Fixed bug: Incorrect values being shown in the Feed Processing metabox.
* Fixed bug: Feed limits set to zero were causing feeds to not be imported.

= 3.9 (2013-12-12) =
* New Feature: Feed sources can have their own update interval.
* New Feature: The time remaining until the next update has been added to the Feed Source table.

= 3.8 (2013-12-05) =
* New Feature: Feed items can be limited and deleted by their age.
* Enhanced: Added utility functions for shorter filters.
* Fixed bug: License codes were being erased when add-ons were deactivated.
* Fixed bug: Some feed sources could not be set to active from the table controls.
* Fixed bug: str_pos errors appear when custom feed url is left empty.
* Fixed bug: Some options were producing undefined index errors.

= 3.7 (2013-11-28) =
* New Feature: State system - Feed sources can be activated/paused.
* New Feature: State system - Feed sources can be set to activate or pause themselves at a specific date and time.
* Enhanced: Added compatibility with nested outline elements in OPML files.
* Enhanced: Admin menu icon image will change into a Dashicon, when WordPress is updated to 3.8 (Decemeber 2013).
* Fixed bug: Custom Post types were breaking when the plugin is activated.

= 3.6.1 (2013-11-17) =
* Fixed bug: Missing 2nd argument for wprss_shorten_title()

= 3.6 (2013-11-16) =
* New Feature: Can set the maximum length for titles. Long titles get trimmed.
* Fixed bug: Fixed errors with undefined indexes for unchecked checkboxes in the settings page.
* Fixed bug: Pagination on front static page was not working.

= 3.5.2 (2013-11-11) =
* Fixed bug: Invalid feed source url was producing an Undefined method notice.
* Fixed bug: Custom feed was producing a 404 page.
* Fixed bug: Presstrends code firing on admin_init, opt-in implementation coming soon

= 3.5.1 (2013-11-09) =
* Enhanced: Increased compatibility with RSS sources.
* Fixed bug: Pagination not working on home page

= 3.5 (2013-11-6) =
* New Feature: Can delete feed items for a particular source
* Enhanced: the 'Fetch feed items' row action for feed sources resets itself after 3.5 seconds.
* Enhanced: The feed image is saved for each url.
* Fixed bug: Link to source now links to correct url. Previously linked to site's feed.

= 3.4.6 (2013-11-1) =
* Enhanced: Added more hooks to debugging page for the Feed to Post add-on.
* Fixed bug: Uninitialized loop index

= 3.4.5 (2013-10-30) =
* Bug Fix: Feed items were not being imported while the WPML plugin was active.

= 3.4.4 (2013-10-26) =
* New feature: Pagination
* New feature: First implementation of editor button for easy shortcode creation
* Enhanced: Feed items and sources don't show up in link manager
* Enhanced: Included Presstrends code for plugin usage monitoring

= 3.4.3 (2013-10-20) =
* Fixed bug: Removed anonymous functions for backwards PHP compatibility
* Bug fix: Added suppress_filters in feed-display.php to prevent a user reported error
* Bug fix: Missing <li> in certain feed displays

= 3.4.2 (2013-9-19) =
* Enhanced: Added some hooks for Feed to Post compatibility
* Enhanced: Moved date settings to a more appropriate location

= 3.4.1 (2013-9-16) = 
* Fixed Bug: Minor issue with options page - PHP notice

= 3.4 (2013-9-15) =
* New Feature: Saving/Updating a feed source triggers an update for that source's feed items.
* New Feature: Option to change Youtube, Vimeo and Dailymotion feed item URLs to embedded video players URLs
* New Feature: Facebook Pages URLs are automatically detected and changed into Atom Feed URLs using FB's Graph
* Enhanced: Updated jQuery Colorbox library to 1.4.29
* Fixed Bug: Some settings did not have a default value set, and were throwing an 'Undefined Index' error
* Fixed Bug: Admin notices do not disappear immediately when dismissed.

= Version 3.3.3 (2013-09-08) =
* Fixed bug: Better function handling on uninstall, should remove uninstall issues

= Version 3.3.2 (2013-09-07) =
* New feature: Added exclude parameter to shortcode
* Enhanced: Added metabox links to documentation and add-ons
* Fixed bug: Custom feed linking to post on user site rather than original source
* Fixed bug: Custom post types issues when activitating the plugin

= Version 3.3.1 (2013-08-09) =
* Fixed Bug: Roles and Capabilities file had not been included
* Fixed Bug: Error on install, function not found

= Version 3.3 (2013-08-08) =
* New feature: OPML importer
* New feature: Feed item limits for individual Feed Sources
* New feature: Custom feed URL
* New feature: Feed limit on custom feed
* New feature: New 'Fetch feed items' action for each Feed Source in listing display
* New feature: Option to enable link to source
* Enhanced: Date strings now change according to locale being used (i.e. compatible with WPML)
* Enhanced: Capabilities implemented
* Enhanced: Feed Sources row action 'View' removed
* Fixed Bug: Proxy feed URLs resulting in the permalink: example.com/url

= Version 3.2 (2013-07-06) =
* New feature: Parameter to limit number of feeds displayed
* New feature: Paramter to limit feeds displayed to particular sources (via ID)
* Enhanced: Better feed import handling to handle large number of feed sources

= Version 3.1.1 (2013-06-06) =
* Fixed bug: Incompatibility with some other plugins due to function missing namespace

= Version 3.1 (2013-06-06) =
* New feature: Option to set the number of feed items imported from every feed (default 5)
* New feature: Import and Export aggregator settings and feed sources
* New feature: Debugging page allowing manual feed refresh and feed reset
* Enhanced: Faster handling of restoring sources from trash when feed limit is 0
* Fixed bug: Limiter on number of overall feeds stored not working
* Fixed bug: Incompatibility issue with Foobox plugin fixed 
* Fixed bug: Duplicate feeds sometimes imported

= Version 3.0 (2013-03-16) =
* New feature: Option to select cron frequency
* New feature: Code extensibility added to be compatible with add-ons
* New feature: Option to set a limit to the number of feeds stored (previously 50, hard coded)
* New feature: Option to define the format of the date shown below each feed item
* New feature: Option to show or hide source of feed item
* New feature: Option to show or hide publish date of feed item
* New feature: Option to set text preceding publish date
* New feature: Option to set text preceding source of feed item
* New feature: Option to link title or not
* New feature: Limit of 5 items imported for each source instead of 10
* Enhanced: Performance improvement when publishing * New feeds in admin
* Enhanced: Query tuning for better performance
* Enhanced: Major code rewrite, refactoring and inclusion of hooks
* Enhanced: Updated Colorbox to v1.4.1
* Enhanced: Better security implementations	
* Enhanced: Better feed preview display
* Fixed bug: Deletion of items upon source deletion not working properly
* Requires: WordPress 3.3

= Version 2.2.3 (2012-11-01) =
* Fixed bug: Tab navigation preventing typing in input boxes
* Removed: Feeds showing up in internal linking pop up

= Version 2.2.2 (2012-10-30) =
* Removed: Feeds showing up in site search results
* Enhanced: Better tab button navigation when adding a new feed
* Enhanced: Better guidance when a feed URL is invalid

= Version 2.2.1 (2012-10-17) =
* Fixed bug: wprss_feed_source_order assumes everyone is an admin

= Version 2.2 (2012-10-01) =
* Italian translation added
* Feed source order changed to alphabetical
* Fixed bug - repeated entries when having a non-valid feed source
* Fixed bug - all imported feeds deleted upon trashing a single feed source

= Version 2.1 (2012-09-27) =
* Now localised for translations
* Fixed bug with date string
* Fixed $link_before and $link_after, now working
* Added backwards compatibility for wp_rss_aggregator() function

= Version 2.0 (2012-09-21) =
* Bulk of code rewritten and refactored
* Added install and upgrade functions
* Added DB version setting
* Feed sources now stored as Custom Post Types
* Feed source list sortable ascending or descending by name
* Removed days subsections in feed display
* Ability to limit total number of feeds displayed
* Feeds now fetched via Cron
* Cron job to delete old feed items, keeps max of 50 items in DB
* Now requires WordPress 3.2
* Updated colorbox to v1.3.20.1
* Limit of 15 items max imported for each source
* Fixed issue of page content displaying incorrectly after feeds

= Version 1.1 (2012-08-13) =
* Now requires WordPress 3.0
* More flexible fetching of images directory
* Has its own top level menu item
* Added settings section
* Ability to open in lightbox, new window or default browser behaviour
* Ability to set links as follow or no follow
* Using constants for oftenly used locations
* Code refactoring
* Changes in file and folder structure

= Version 1.0 (2012-01-06) =
* Initial release.