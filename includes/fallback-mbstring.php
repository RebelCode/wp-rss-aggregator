<?php
class WPRSS_MBString {

	public static function mb_substr( $str, $start, $count = 'end' ) {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $str, $start );
		}

		if ( $start != 0 ) {
			$split = self::mb_substr_split_unicode( $str, intval( $start ) );
			$str = substr( $str, $split );
		}

		if ( $count !== 'end' ) {
			$split = self::mb_substr_split_unicode( $str, intval( $count ) );
			$str = substr( $str, 0, $split );
		}

		return $str;
	}

	public static function mb_substr_split_unicode( $str, $splitPos ) {
		if ( $splitPos == 0 ) {
			return 0;
		}

		$byteLen = strlen( $str );

		if ( $splitPos > 0 ) {
			if ( $splitPos > 256 ) {
			// Optimize large string offsets by skipping ahead N bytes.
			// This will cut out most of our slow time on Latin-based text,
			// and 1/2 to 1/3 on East European and Asian scripts.
				$bytePos = $splitPos;
				while ( $bytePos < $byteLen && $str[$bytePos] >= "\x80" && $str[$bytePos] < "\xc0" ) {
					++$bytePos;
				}
				$charPos = mb_strlen( substr( $str, 0, $bytePos ) );
			} else {
				$charPos = 0;
				$bytePos = 0;
			}

			while ( $charPos++ < $splitPos ) {
				++$bytePos;
				// Move past any tail bytes
				while ( $bytePos < $byteLen && $str[$bytePos] >= "\x80" && $str[$bytePos] < "\xc0" ) {
					++$bytePos;
				}
			}
		} else {
			$splitPosX = $splitPos + 1;
			$charPos = 0; // relative to end of string; we don't care about the actual char position here
			$bytePos = $byteLen;
			while ( $bytePos > 0 && $charPos-- >= $splitPosX ) {
				--$bytePos;
				// Move past any tail bytes
				while ( $bytePos > 0 && $str[$bytePos] >= "\x80" && $str[$bytePos] < "\xc0" ) {
					--$bytePos;
				}
			}
		}

		return $bytePos;
	}

	public static function mb_strlen( $str, $enc = '' ) {
		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $str );
		}

		$counts = count_chars( $str );
		$total = 0;

		// Count ASCII bytes
		for ( $i = 0; $i < 0x80; $i++ ) {
			$total += $counts[$i];
		}

		// Count multibyte sequence heads
		for ( $i = 0xc0; $i < 0xff; $i++ ) {
			$total += $counts[$i];
		}
		return $total;
	}

	public static function mb_strpos( $haystack, $needle, $offset = 0, $encoding = '' ) {
		if ( function_exists( 'mb_strpos' ) ) {
			return mb_strpos( $haystack, $needle, $offset );
		}

		$needle = preg_quote( $needle, '/' );

		$ar = array();
		preg_match( '/' . $needle . '/u', $haystack, $ar, PREG_OFFSET_CAPTURE, $offset );

		if ( isset( $ar[0][1] ) ) {
			return $ar[0][1];
		} else {
			return false;
		}
	}

	public static function mb_strrpos( $haystack, $needle, $offset = 0, $encoding = '' ) {
		if ( function_exists( 'mb_strrpos' ) ) {
			return mb_strrpos( $haystack, $needle, $offset );
		}

		$needle = preg_quote( $needle, '/' );

		$ar = array();
		preg_match_all( '/' . $needle . '/u', $haystack, $ar, PREG_OFFSET_CAPTURE, $offset );

		if ( isset( $ar[0] ) && count( $ar[0] ) > 0 && isset( $ar[0][count( $ar[0] ) - 1][1] ) ) {
			return $ar[0][count( $ar[0] ) - 1][1];
		} else {
			return false;
		}
	}
}