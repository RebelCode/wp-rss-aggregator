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