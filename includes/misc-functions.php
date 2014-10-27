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


/**
 * Trims the given text by a fixed number of words, and preserving HTML.
 *
 * Collapses all white space, trims the text up to a certain number of words, and
 * preserves all HTML markup. HTML tags do not count as words.
 * Uses WordPress `wp_trim_words` internally.
 * Uses mostly trivial regex. Works by removing, then re-adding tags.
 * Just as well closes open tags by counting them.
 * 
 * @param string $text The text to trim.
 * @param string $max_words The maximum number of words.
 * @param array $allowed_tags The allows tags. Regular array of tag names.
 * @return string The trimmed text.
 */
function wprss_trim_words( $text, $max_words, $allowed_tags = array() ) {	
	// See http://haacked.com/archive/2004/10/25/usingregularexpressionstomatchhtml.aspx/
	$html_regex = <<<EOS
(</?(\w+)(?:(?:\s+\w+(?:\s*=\s*(?:".*?"|'.*?'|[^'">\s]+))?)+\s*|\s*)/?>)
EOS;
	$html_regex_str = sprintf ('!%1$s!', $html_regex );
	// Collapsing single-line white space
	$text = preg_replace( '!\s+!', ' ', $text );

	// Enum of tag types
	$tag_type = array(
		'opening'		=> 1,
		'closing'		=> 2,
		'self-closing'	=> 0
	);
	
	/*
	 * Split text using tags as delimiters.
	 * The resulting array is a sequence of elements as follows:
	 * 	0 - The complete tag that it was delimited by
	 * 	1 - The name of that tag
	 * 	2 - The text that follows it until the next tag
	 * 
	 * Each element contains 2 indexes:
	 * 	0 - The element content
	 * 	1 - The position in the original string, at which it was found
	 *
	 * For instance:
	 *		<span>hello</span> how do <em>you do</em>?
	 *
	 * Will result in an array (not actaul structure) containing:
	 * <span>, span, hello, </span>, span, how do, <em>, em, you do, </em>, em, ?
	 */
	$text_array = preg_split(
		$html_regex_str,				// Match HTML Regex above
		$text,							// Split the text
		-1,								// No split limit
		// FLAGS
			PREG_SPLIT_DELIM_CAPTURE	// Capture delimiters (html tags)
		|	PREG_SPLIT_OFFSET_CAPTURE	// Record the string offset of each part
	);
	/*
	 * Get first element of the array (leading text with no HTML), and add it to a string.
	 * This string will contain the plain text (no HTML) only after the follow foreach loop.
	 */
	$text_start = array_shift( $text_array );
	$plain_text = $text_start[0];

	/*
	 * Chunk the array in groups of 3. This will take each 3 consecutive elements
	 * and group them together.
	 */
	$pieces = array_chunk( $text_array, 3 );


	/*
	 * Iterate over each group and:
	 *	1. Generate plain text without HTML
	 *	2. Add apropriate tag type to each group
	 */
	foreach ( $pieces as $_idx => $_piece ) {
		// Get the data
		$tag_piece = $_piece[0];
		$text_piece = $_piece[2];
		// Compile all plain text together
		$plain_text .= $text_piece[0];
		// Check the tag and assign the proper tag type
		$tag = $tag_piece[0];
		$pieces[ $_idx ][1][2] =
			( substr( $tag, 0, 2 ) === '</' )?
				$tag_type['closing'] :
			( substr( $tag, strlen( $tag ) - 3, 2 ) == '/>' )?
				$tag_type['self-closing'] :
				$tag_type['opening'];
	}

	// Stock trimming of words
	$plain_text = wp_trim_words_et( $plain_text, $max_words );

	/*
	 * Put the tags back, using the offsets recorded
	 * This is where the sweet magic happens
	 */

	// Cache to only check `in_array` once for each tag type
	$allowed_tags_cache = array();
	// For counting open tags
	$tags_to_close = array();
	// Since some tags will not be included...
	$tag_position_offset = 0;
	$text = $plain_text;

	// Iterate the groups once more
	foreach ( $pieces as $_idx => $_piece ) {
		// Tag and tagname
		$_tag_piece = $_piece[0];
		$_tag_name_piece = $_piece[1];
		// Name of the tag
		$_tag_name = strtolower( $_tag_name_piece[0] );
		// Tag type
		$_tag_type = $_tag_name_piece[2];
		// Text of the tag
		$_tag = $_tag_piece[0];
		// Position of the tag in the original string
		$_tag_position = $_tag_piece[1];
		$_actual_tag_position = $_tag_position - $tag_position_offset;

		// Caching result
		if ( !isset( $allowed_tags_cache[$_tag_name] ) )
			$allowed_tags_cache[$_tag_name] = in_array( $_tag_name, $allowed_tags );

		// Whether to stop (tag position is outside the trimmed text)
		if( $_actual_tag_position >= strlen( $text ) ) break;

		// Whether to skip tag
		if ( !$allowed_tags_cache[$_tag_name] ) {
			$tag_position_offset += strlen( $_tag ); // To correct for removed chars
			continue;
		}

		// If the tag is an opening tag, record it in $tags_to_close
		if( $_tag_type === $tag_type['opening'] )
			array_push( $tags_to_close, $_tag_name );
		// If it is a closing tag, remove it from $tags_to_close
		elseif( $_tag_type === $tag_type['closing'] )
			array_pop( $tags_to_close );

		// Inserting tag back into place
		$text = substr_replace( $text, $_tag, $_actual_tag_position, 0);
	}

	// Add the appropriate closing tags to all unclosed tags
	foreach( $tags_to_close as $_tag_name ) {
		$text .= sprintf('</%1$s>', $_tag_name);
	}
	
	return $text;
}