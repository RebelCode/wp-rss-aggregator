<?php

function wprss_is_url_youtube($url)
{
    $parsed = is_array($url) ? $url : wpra_parse_url($url);

    return isset($parsed['host']) && stripos($parsed['host'], 'youtube.com') !== false;
}

function wprss_is_feed_youtube($feed)
{
    $id = ($feed instanceof WP_Post) ? $feed->ID : $feed;
    $url = get_post_meta($id, 'wprss_url', true);

    return wprss_is_url_youtube($url);
}

// Filters URLs to allow WPRA to be able to use YouTube channel URLs as feed URLs
add_filter('wpra/importer/feed/url', function ($url, $parsed) {
    $pathArray = $parsed['path'];
    $channelPos = array_search('channel', $pathArray);

    // Check if a Youtube URL and the "channel" part was found in the URL path
    if (stripos($parsed['host'], 'youtube.com') === false || $channelPos === false) {
        return $url;
    }

    // Check if there's another part that follows the "channel" part in the URL path
    if (!empty($pathArray[$channelPos + 1])) {
        // Use it to construct the Youtube feed URL
        return sprintf(
            'https://www.youtube.com/feeds/videos.xml?channel_id=%s',
            $pathArray[$channelPos + 1]
        );
    }

    return $url;
}, 10, 2);
