<?php

use Aventura\Wprss\Core\Caching\ImageCache;

// Save item image info during import
add_action('wprss_items_create_post_meta', 'wpra_import_item_images', 10, 3);

/**
 * Imports images for a feed item.
 *
 * The "import" process here basically just fetches the images from the item's content/excerpt, the media:thumbnail
 * tag and the enclosures. The entire list of images is saved, along with the URL of the best image.
 *
 * @param int|string     $itemId   The ID of the feed item.
 * @param SimplePie_Item $item     The simple pie item object.
 * @param int|string     $sourceId The ID of the feed source from which the item was imported.
 */
function wpra_import_item_images($itemId, $item, $sourceId)
{
    $logger = wpra_get_logger($sourceId);
    $logger->debug('Importing images for item "{title}"', ['title' => $item->get_title()]);

    // Get all of the item's images
    $allImages = wpra_get_item_images($item);
    // Process the images, removing duds, and find the best image
    $images = wpra_process_images($allImages, $bestImage);

    // Change null to an empty string before saving in meta
    $bestImage = ($bestImage === null) ? '' : $bestImage;

    // Save the images in meta
    update_post_meta($itemId, 'wprss_images', $images);
    update_post_meta($itemId, 'wprss_thumbnail_image', $bestImage);

    // Log number of found images
    $count = count($images);
    $logger->info('Found {count} images', ['count' => count($images)]);

    // Log best image found
    if ($count > 0) {
        $logger->info('Found best image: "{url}"', ['url' => $bestImage]);
    }
}

/**
 * Retrieves the URLs of all the images in a feed item.
 *
 * @param SimplePie_Item $item The simple pie item object.
 *
 * @return string[] A list of image URLs.
 */
function wpra_get_item_images($item)
{
    // Detect images and save them
    $images = [];
    $images[] = wpra_get_item_media_thumbnail_image($item);
    $images += wpra_get_item_content_images($item);
    $images += wpra_get_item_enclosure_images($item);

    // Filter out empty images
    return array_filter($images, function ($image) {
        return !empty($image);
    });
}

/**
 * Processes a list of image URLs to strip away images that are unreachable or too small, as well as identify which
 * image in the list is the best image (in terms of dimensions and aspect ratio).
 *
 * @param      $images
 * @param null $bestImage
 *
 * @return mixed
 */
function wpra_process_images($images, &$bestImage = null)
{
    // The final list of images
    $finalImages = [];
    // The largest image size found so far, as width * height
    $maxSize = 0;

    // The minimum dimensions for an image to be valid
    $minWidth = apply_filters('wprss_thumbnail_min_width', 50);
    $minHeight = apply_filters('wprss_thumbnail_min_height', 50);

    foreach ($images as $imageUrl) {
        // Try to download the image, skip image on failure
        if (is_wp_error($tmp_img = wpra_download_image($imageUrl))) {
            continue;
        }

        try {
            $dimensions = ($tmp = $tmp_img->get_local_path())
                ? $tmp_img->get_size()
                : null;

            // Ignore image if too small in either dimension
            if ($dimensions === null || $dimensions[0] < $minWidth || $dimensions[1] < $minHeight) {
                continue;
            }

            $area = $dimensions[0] * $dimensions[1];
            $ratio = floatval($dimensions[0]) / floatval($dimensions[1]);

            // If larger than the current best image and its aspect ratio is between 1 and 2,
            // then set this image as the new best image
            if ($area > $maxSize && $ratio > 1.0 && $ratio < 2.0) {
                $bestImage = $imageUrl;
            }

            // Add to the list of images to save
            $finalImages[] = $imageUrl;
        } catch (Exception $exception) {
            // If failed to get dimensions, skip the image
            continue;
        }
    }

    return $images;
}

/**
 * Returns the <media:thumbnail> image for the given feed item.
 *
 * @since [*next-version*]
 *
 * @param SimplePie_Item $item The feed item
 *
 * @return string|null The string URL of the image, or null if the item does not contain a <media:thumbnail> image.
 */
function wpra_get_item_media_thumbnail_image($item)
{
    // Try to get image from enclosure if available
    $enclosure = $item->get_enclosure();

    // Stop if item has no enclosure tag
    if (is_null($enclosure)) {
        return null;
    }

    // Stop if enclosure tag has no link
    $url = $enclosure->get_link();
    if (empty($url)) {
        return null;
    }

    // Stop if image cannot be downloaded
    $image = wpra_download_image($url);
    if (is_wp_error($image)) {
        return null;
    }

    if ($image->get_local_path()) {
        return $url;
    }

    return null;
}

/**
 * Returns the enclosure image for the given feed item.
 *
 * @since [*next-version*]
 *
 * @param SimplePie_Item $item The feed item
 *
 * @return string[] The string URLs of the found enclosure images.
 */
function wpra_get_item_enclosure_images($item)
{
    $enclosure = $item->get_enclosure();

    // Stop if item has no enclosure
    if (is_null($enclosure)) {
        return [];
    }

    // Get all the thumbnails from the enclosure
    $thumbnails = (array)$enclosure->get_thumbnails();

    return $thumbnails;
}

/**
 * Returns the first image found in the given item's content
 *
 * @since [*next-version*]
 *
 * @param SimplePie_Item $item The feed item
 *
 * @return string[] Returns the string URLs of the images found.
 */
function wpra_get_item_content_images($item)
{
    // Extract all images from the content into the $matches array
    preg_match_all('/<img.*?src=[\'"](.*?)[\'"].*?>/xis', $item->get_content(), $matches);

    $i = 0;
    $images = [];
    while (!empty($matches[1][$i])) {
        $imageUrl = urldecode(trim($matches[1][$i]));
        // Increment early to allow the iteration body to use "continue" statements
        $i++;

        // Add http prefix if not included
        if (stripos($imageUrl, '//') === 0) {
            $imageUrl = 'http:' . $imageUrl;
        }

        // Maybe fix the image URL for small facebook images
        $imageUrl = wpra_maybe_get_large_facebook_image($imageUrl);

        // Add to the list
        $images[] = $imageUrl;
    }

    return $images;
}

/**
 * Checks if the image at the given URL is provided by Facebook's CDN and attempts to retrieve the large version.
 *
 * @since [*next-version*]
 *
 * @param string $url The URL of the image.
 *
 * @return string The URL of the larger version of the image if the image was provided by Facebook and a larger
 *                version is available, or the original parameter URL otherwise.
 */
function wpra_maybe_get_large_facebook_image($url)
{
    // Check if image is provided from Facebook's CDN, and if so remove any "_s" small image extension in the URL
    if (stripos($url, 'fbcdn') > 0) {
        $imageExt = strrchr($url, '.');
        $largerImgUrl = str_replace('_s' . $imageExt, '_n' . $imageExt, $url);
        // If the larger image exists, set the url to point to it
        if (wpra_remote_file_exists($largerImgUrl)) {
            $url = $largerImgUrl;
        }
    }

    // If the URL is from 'fbexternal-a.akamaihd.net', we can use a GET param to get the actual image url
    if (parse_url($url, PHP_URL_HOST) === 'fbexternal-a.akamaihd.net') {
        // Get the query string
        $queryStr = parse_url($url, PHP_URL_QUERY);
        // If not empty
        if ($queryStr !== '') {
            // Parse it
            parse_str(urldecode($queryStr), $output);

            // If it has a url GET param, use it as the image URL
            if (isset($output['amp;url'])) {
                $output['url'] = $output['amp;url'];
            }
            if (isset($output['url'])) {
                $url = urldecode($output['url']);
            }
        }
    }

    return $url;
}

/**
 * Retrieves the cache TTL.
 *
 * This value defaults to the value of the WPRSS_ET_IMAGE_CACHE_TTL constant.
 * It can be modified by implementing a handler for the `wprss_et_image_cache_ttl` filter.
 *
 * @since [*next-version*]
 *
 * @return int The number of seconds representing the cache time to live.
 */
function wpra_get_image_cache_ttl()
{
    return apply_filters('wprss_et_image_cache_ttl', WEEK_IN_SECONDS);
}

/**
 * Retrieves the cache controller.
 *
 * This value can be modified by implementing a handler for the `wprss_et_image_cache` filter.
 *
 * @since [*next-version*]
 *
 * @see   wpra_get_image_cache_ttl()
 *
 * @return ImageCache The instance of the cache controller.
 */
function wpra_get_image_cache()
{
    static $cache = null;

    if (is_null($cache)) {
        $cache = new ImageCache();
        $cache->set_ttl(wpra_get_image_cache_ttl());
    }

    return apply_filters('wprss_et_image_cache', $cache);
}

/**
 * Retrieves an image identified by it's URL from cache.
 *
 * If cache doesn't exist, or is expired, image will be downloaded first.
 * This value can be overridden by implementing a handler for the `wprss_et_downloaded_image` filter.
 *
 * @since [*next-version*]
 *
 * @see   wpra_get_image_cache()
 *
 * @param string $url The URL of the image to download
 *
 * @return ImageCache\Image The instance of the retrieved image
 */
function wpra_download_image($url)
{
    if (empty($url)) {
        return apply_filters(
            'wprss_et_downloaded_image',
            new WP_Error('wprss_et_download_image_failed', __('Image URL cannot be empty'))
        );
    }

    try {
        $image = wpra_get_image_cache()->get_images($url);
    } catch (Exception $e) {
        $message = $e->getMessage();
        $image = new WP_Error('wprss_et_download_image_failed', $message, $url);

        wpra_get_logger()->warning(
            'Image could not be downloaded from {url}. Error: {error}',
            [
                'url' => $url,
                'error' => $message,
            ]
        );
    }

    return apply_filters('wprss_et_downloaded_image', $image);
}

/**
 * Checks if a remote file exists, by pinging it and checking the status code.
 *
 * @since [*next-version*]
 *
 * @param string $url The url of the remote resource
 *
 * @return bool True if the remote file exists, false if not.
 */
function wpra_remote_file_exists($url)
{
    $exists = false;

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    $response = curl_exec($curl);

    if ($response !== false && curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
        $exists = true;
    }

    curl_close($curl);

    return $exists;
}
