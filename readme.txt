=== WP RSS Aggregator ===
Contributors: jeangalea
Donate link: http://www.jeangalea.com
Tags: rss, feeds, aggregation, aggregator, import
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 1.1
Imports and merges multiple RSS Feeds using SimplePie. Outputs feeds sorted by date (latest first).

== Description ==

WP RSS Aggregator helps you create a feed reader on your WordPress site. It works in a similar fashion to RSS readers like for example Netvibes.
You can add any number of feeds through an administration panel, the plugin will then pull all the feeds from these sites, merge them and sort them by date.

The output will be organised like this:

* Today
* Yesterday
* 2 Days ago
* More than 2 days ago

The plugin uses SimplePie for the feed operations. The actual news feeds are not stored in your databases but only cached for faster response times.
You can call the function from within the theme or even use a shortcode with parameters.

= Demo =
The plugin can be seen in use on the [WPMayor.com WordPress News page](http://www.wpmayor.com/wordpress-news/)

= Credit = 
Created by Jean Galea. Need a [Web Developer](http://www.jeangalea.com/services/wordpress-consultancy-development/)?

== Installation ==

1. Upload the `wp-rss-aggregator` folder to the `/wp-content/plugins/` directory
2. Activate the WP RSS Aggregator plugin through the 'Plugins' menu in WordPress
3. Configure the plugin by going to the `RSS Aggregator` menu item that appears in your dashboard menu.
3. Use the shortcode in your posts or pages: `[wp_rss_aggregator]`

The parameters accepted are:

* date_before 
* date_after 
* links_befor
* links_after
* link_before
* link_after

An example of a shortcode with parameters:
`[wp_rss_aggregator date_before='<h2>' date_after='</h2>']`
It is advisable to use the 'HTML' view of the editor when inserting the shortcode with paramters.


An example of a function call from within the template files:
`<?php
wp_rss_aggregator(
'date_before' => '<h3>',
'date_after' => '</h3>',
'links_before' => '<ul>',
'links_after' => '</ul>',
'link_before' => '<li>',
'link_after' => '</li>'
);
?>`

You can also set whether the feed links should open in a new window, current window or even a lightbox, via the settings panel. 

The settings panel also has an option to set links as nofollow for SEO purposes.

== Frequently Asked Questions ==
= How can I output the feeds in my theme? =

You can either call the function directly within the theme:
`<?php wp_rss_aggregator(); ?>`

Or use the shortcode in your posts and pages:
[wp_rss_aggregator]

== Screenshots ==

1. The output of this plugin on the frontend, as seen on www.wpmayor.com.

2. Admin administration panel.

3. The settings page.

== Changelog ==


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

