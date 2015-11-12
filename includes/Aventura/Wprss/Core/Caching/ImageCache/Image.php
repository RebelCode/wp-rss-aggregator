<?php

namespace Aventura\Wprss\Core\Caching\ImageCaching;

if (!class_exists('\\WPRSS_Image_Cache_Image')) {
	require_once WPRSS_INC . 'image-caching.php';
}

/**
 * Image class for ImageCaching module.
 */
class Image extends \WPRSS_Image_Cache_Image {

}
