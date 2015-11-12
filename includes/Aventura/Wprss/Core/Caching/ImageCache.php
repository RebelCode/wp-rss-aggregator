<?php

namespace Aventura\Wprss\Core\Caching;

/**
 * Image caching class.
 */
class ImageCache extends WPRSS_Image_Cache {

	protected function _construct() {
		$this->set_image_class_name( __NAMESPACE__ . '\\ImageCache\\Image' );
	}

}
