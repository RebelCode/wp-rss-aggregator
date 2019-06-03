<?php

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
    $images = [wpra_get_item_media_thumbnail_image($item)];
    $images += wpra_get_item_content_images($item);
    $images += wpra_get_item_enclosure_images($item);

    return $images;
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
    $imgContainer = wpra_container()->get('wpra/images/container');

    // The final list of images
    $finalImages = [];
    // The largest image size found so far, as width * height
    $maxSize = 0;

    // The minimum dimensions for an image to be valid
    $minWidth = apply_filters('wprss_thumbnail_min_width', 50);
    $minHeight = apply_filters('wprss_thumbnail_min_height', 50);

    foreach ($images as $imageUrl) {
        try {
            /* @var $tmp_img WPRSS_Image_Cache_Image */
            $tmp_img = $imgContainer->get($imageUrl);

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

    return $finalImages;
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

    // Check if image can be downloaded
    $imgContainer = wpra_container()->get('wpra/images/container');
    try {
        /* @var $image WPRSS_Image_Cache_Image */
        $image = $imgContainer->get($url);
    } catch (Exception $exception) {
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
    $thumbnails = (array) $enclosure->get_thumbnails();

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

        // Add to the list
        $images[] = $imageUrl;
    }

    return $images;
}
