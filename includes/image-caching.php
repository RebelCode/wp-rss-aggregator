<?php

/**
 * @since 4.6.10
 */
class WPRSS_Image_Cache {
	
	protected $_download_request_timeout = 300;
	
	protected $_image_class_name = 'WPRSS_Image_Cache_Image';
	protected $_images = array();
	
	/**
	 * @since 4.6.10
	 */
	public function __construct() {
	}
	
	
	/**
	 * @since 4.6.10
	 * @param string $class_name
	 * @return \WPRSS_Image_Cache This instance.
	 */
	public function set_image_class_name( $class_name ) {
		$this->_image_class_name = $class_name;
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return string
	 */
	public function get_image_class_name() {
		return trim($this->_image_class_name);
	}
	
	
	/**
	 * @since 4.6.10
	 * @param int $timeout
	 * @return \WPRSS_Image_Cache This instance.
	 */
	public function set_download_request_timeout( $timeout ) {
		$this->_download_request_timeout = intval( $timeout );
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return int
	 */
	public function get_download_request_timeout() {
		return $this->_download_request_timeout;
	}
	
	
	/**
	 * @since 4.6.10
	 * @param string $url
	 * @return \WPRSS_Image_Cache_Image
	 * @throws Exception If class invalid, or not found
	 */
	public function get_new_image( $url = null ) {
		$error_caption = 'Could not create new cache image';
		$class_name = $this->get_image_class_name();
		if ( empty( $class_name ) ) throw new Exception( sprintf( '%1$s: class name must not be empty' ) );
		if ( !class_exists( $class_name ) ) throw new Exception( sprintf( '%1$s: class "%2$s" does not exist', $class_name ) );
		
		$image = new $class_name();
		$this->_prepare_image( $image );
		/* @var $image WPRSS_Image_Cache_Image */
		if ( !is_null( $url ) ) $image->set_url( $url );
		
		return $image;
	}
	
	
	/**
	 * @since 4.6.10
	 * @param WPRSS_Image_Cache_Image $image
	 */
	protected function _prepare_image( $image ) {
		$image->set_download_request_timeout( $this->get_download_request_timeout() );
	}
	
	
	/**
	 * @since 4.6.10
	 * @param string|null $url
	 * @return array|WPRSS_Image_Cache_Image|\WP_Error
	 */
	public function get_images( $url = null ) {
		if ( is_null( $url ) ) return $this->_images;
		
		// Gotta cache one
		if ( !isset( $this->_images[ $url ] ) ) {
			try {
				$image = $this->_download_image($url);
			} catch ( Exception $e ) {
				return new WP_Error( 'image_cache_cannot_download', $e->getMessage() );
			}
			
			$this->_images[ $url ] = $image;
		}
		
		return $this->_images[ $url ];
	}
	
	
	/**
	 * @since 4.6.10
	 * @return \WPRSS_Image_Cache This instance
	 */
	public function purge() {
		$image_class_name = $this->get_image_class_name();
		foreach( $this->get_images() as $_url => $_image ) {
			/* @var $_image WPRSS_Image_Cache_Image */
			if ( is_a( $_image, $image_class_name ) )
				$_image->delete();
		}
		
		$this->_images = array();
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @param string $url
	 * @return WPRSS_Image_Cache_Image
	 */
	protected function _download_image( $url ) {
		$image = $this->get_new_image( $url );
		$image->download();
		
		return $image;
	}
}


/**
 * @since 4.6.10
 */
class WPRSS_Image_Cache_Image {
	
	protected $_url;
	protected $_local_path;
	protected $_unique_name;
	protected $_size;
	protected $_download_request_timeout;
	protected $_is_attempted;
	protected $_is_fall_back_to_unsecure;
	
	
	/**
	 * @since 4.6.10
	 * @param string|null $data
	 */
	public function __construct( $data = null ) {
		$this->reset();
		
		if ( is_string( $data ) && !empty( $data ) )
			$this->_set_url( $data );
	}
	
	
	/**
	 * @since 4.6.10
	 * @return \WPRSS_Image_Cache_Image This instance.
	 */
	public function reset() {
		$this->_url = null;
		$this->_local_path = null;
		$this->_unique_name = null;
		$this->_size = null;
		$this->_download_request_timeout = 300;
		$this->_is_attempted = false;
		$this->_is_fall_back_to_unsecure = true;
		
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @param string $url
	 * @return \WPRSS_Image_Cache_Image This instance.
	 */
	protected function _set_url( $url ) {
		$this->_url = $url;
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @param string $url
	 * @return \WPRSS_Image_Cache_Image This instance.
	 */
	public function set_url( $url ) {
		$this->reset();
		$this->_set_url($url);
		
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return string
	 */
	public function get_url() {
		return $this->_url;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return boolean
	 */
	public function has_url() {
		return isset( $this->_url );
	}
	
	
	/**
	 * @since 4.6.10
	 * @param string $path
	 * @return \WPRSS_Image_Cache_Image This instance.
	 */
	protected function _set_local_path( $path ) {
		$this->_local_path = $path;
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return string
	 */
	public function get_local_path() {
		return $this->_local_path;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return boolean
	 */
	public function has_local_path() {
		return isset( $this->_local_path );
	}
	
	
	/**
	 * @since 4.6.10
	 * @param int $timeout
	 * @return \WPRSS_Image_Cache_Image This instance.
	 */
	public function set_download_request_timeout( $timeout ) {
		$this->_download_request_timeout = intval( $timeout );
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return int
	 */
	public function get_download_request_timeout() {
		return $this->_download_request_timeout;
	}
	
	
	/**
	 * @since 4.6.10
	 * @param boolean|null $is_attempted
	 * @return \WPRSS_Image_Cache_Image|boolean Whether was attempted, or this instance.
	 */
	protected function _is_attempted( $is_attempted = null ) {
		if ( is_null( $is_attempted ) )
			return (bool)$this->_is_attempted;
		
		$this->_is_attempted = (bool) $is_attempted;
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return boolean
	 */
	public function is_attempted() {
		return $this->_is_attempted();
	}
	
	
	/**
	 * @since 4.6.10
	 * @param boolean|null $is_fall_back
	 * @return \WPRSS_Image_Cache_Image|boolean Whether will fall back to unsecure, or this instance.
	 */
	public function is_fall_back_to_unsecure( $is_fall_back = null ) {
		if ( is_null( $is_fall_back ) )
			return (bool) $this->_is_fall_back_to_unsecure;
		
		$this->_is_fall_back_to_unsecure = (bool) $is_fall_back;
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return string {@see get_local_path()}
	 * @throws Exception If no URL is set, or the resource is unreadable, or something went wrong.
	 */
	public function download() {
		$error_caption = 'Could not download image';
		if ( !$this->has_url() ) throw new Exception ( sprintf( '%1$s: a URL must be supplied' ) );
		
		// Getting file download lib
		$file_lib_path = ABSPATH . 'wp-admin/includes/file.php';
		if ( !is_readable( $file_lib_path ) ) throw new Exception( sprintf( '%1$s: the file library cannot be read from %2$s', $error_caption, $file_lib_path ) );
		require_once( $file_lib_path );
		
		// Downloading the image
		$url = $this->get_url();
		$timeout = $this->get_download_request_timeout();
		$tmp_path = $this->_download( $url, $timeout );
		if ( is_wp_error( $tmp_path ) ) throw new Exception ( sprintf( '%1$s: %2$s', $error_caption, $tmp_path->get_error_message() ) );
//		wprss_log( sprintf( 'Image saved to "%1$s"', $tmp_path ), null, WPRSS_LOG_LEVEL_SYSTEM );
		$this->_set_local_path( $tmp_path );
		
		return $this->get_local_path();
	}
	
	
	/**
	 * @since 4.6.10
	 * @param string $url
	 * @param int $timeout
	 * @return string|WP_Error The local path to the downloaded image, if successful; an error instance if download failed.
	 */
	protected function _download( $url, $timeout ) {
//		wprss_log( sprintf( 'Downloading from "%1$s"', $url ), null, WPRSS_LOG_LEVEL_SYSTEM );
		$tmp_path = download_url( $url, $timeout );
		if ( is_wp_error( $tmp_path ) ) {
			$https = 'https';
			if ( $this->is_fall_back_to_unsecure() && (stripos( $url, $https ) === 0) ) {
				$url = 'http' . substr( $url, strlen( $https ) );
//				wprss_log( sprintf( 'Downloading from "%1$s"', $url ), null, WPRSS_LOG_LEVEL_SYSTEM );
				$tmp_path = $this->_download( $url, $timeout );
			}
		}
		
		return $tmp_path;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return \WPRSS_Image_Cache_Image This instance.
	 */
	public function delete() {
		if ( $path = $this->get_local_path() ) {
			if ( file_exists( $path ) ) {
				unlink( $path );
			}
		}
		
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return string
	 */
	public function get_unique_name() {
		if( !isset($this->_unique_name) ) {
			$url = $this->get_url();
			// Extract filename from url for title (ignoring query string)
			// One of more character that is not a '?', followed by an image extension
			preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches );
			$url_filename = basename( urldecode( $matches[0] ) );
			// Check for extension. If not found, use last component of the URL
			if ( !isset( $matches[1] ) ) {
				$matches = array();
				// Get the path to the image, without the domain. ex. /news/132456/image
				preg_match_all( '/[^:]+:\/\/[^\/]+\/(.+)/', $url, $matches );
				// If found
				if ( isset( $matches[1][0] ) ) {
					// Replace all '/' into '.' for the filename
					$url_filename = str_replace( '/', '-', $matches[1][0] );
				}
				// If not found
				else {
					// Use a random string as a fallback, with length of 16 characters
					$url_filename = wprss_ftp_generate_random_string( 16 );
				}
			}
			$this->_set_unique_name( $url_filename );
		}
		
		return $this->_unique_name;
	}

	
	/**
	 * @since 4.6.10
	 * @param string $name
	 * @return \WPRSS_Image_Cache_Image This instance.
	 */
	protected function _set_unique_name( $name ) {
		$this->_unique_name = $name;
		return $this;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return array A numeric array, where index 0 holds image width, and index 1 holds image height.
	 * @throws Exception If image file is unreadable.
	 */
	public function get_size() {
		if ( !isset( $this->_size ) ) {
			$error_caption = 'Could not get image size';
			if ( !$this->is_readable() ) throw new Exception( sprintf( '%1$s: image file is not readable' ) );
			$path = $this->get_local_path();
			
			// Trying simplest way
			if ( $size = getimagesize( $path ) )
				$this->_size = array( 0 => $size[0], 1 => $size[1] );
			
			wprss_log( sprintf( 'Tried `getimagesize()`: %1$s', empty($this->_size) ? 'failure' : 'success' ), __METHOD__, WPRSS_LOG_LEVEL_SYSTEM );
			
			if( !$this->_size && function_exists( 'gd_info' ) ) {
				$image = file_get_contents( $path );
				$image = imagecreatefromstring( $image );
				$width = imagesx( $image );
				$height = imagesy( $image );
				$this->_size = array( 0 => $width, 1 => $height );
				wprss_log( sprintf( 'Tried GD: %1$s', empty($this->_size) ? 'failure' : 'success' ), __METHOD__, WPRSS_LOG_LEVEL_SYSTEM );
			}
		}
		
		return $this->_size;
	}
	
	
	/**
	 * @since 4.6.10
	 * @return boolean
	 */
	public function is_readable() {
		return is_readable( $this->get_local_path() );
	}
}