<?php
/**
 * WPRSS Let To Num
 *
 * Does Size Conversions
 *
 * @since 3.1
 * @author Chris Christoff
 * @return $ret
 */
function wprss_let_to_num( $v ) {
	$l   = substr( $v, -1 );
	$ret = substr( $v, 0, -1 );

	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
			break;
	}

	return $ret;
}




/**
 * An enhanced version of WP's media_sideload_image function.
 *
 * If media_sideload_image fails, the file is downloaded manually
 * as an image, inserted as an attachment, and attached to the post.
 * 
 * @since 3.5.1
 */
function wprss_media_sideload_image( $file, $post_id, $desc = null ) {
	try {

		if ( ! empty( $file ) ) {

			// Download file to temp location
			$tmp = download_url( $file );

			// Set variables for storage
			// fix file filename for query strings
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );

			if ( count( $matches ) > 0 ) {
				$file_array['name'] = basename( $matches[0] );
			}
			else {
				preg_match( '/[\/\?\=\&]([^\/\?\=\&]*)[\?]*$/i', $file, $matches2 );
				if ( count( $matches2 ) > 1 ) {
					$file_array['name'] = $matches2[1] . '.png';
				} else {
					@unlink( $tmp );
					return "<img src='$file' alt='' />";
				}
			}
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
			}

			// do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id, $desc );
			// If error storing permanently, unlink
			if ( is_wp_error($id) ) {
				@unlink( $file_array['tmp_name'] );
				return "<img src='$file' alt='' />";
			}

			$src = wp_get_attachment_url( $id );
		}

		// Finally check to make sure the file has been saved, then return the html
		if ( ! empty( $src ) ) {
			$alt = isset( $desc )? esc_attr($desc) : '';
			$html = "<img src='$src' alt='$alt' />";
			return $html;
		}

	}
	catch( Exception $e ) {
		return "<img src='$file' alt='' />";
	}
}