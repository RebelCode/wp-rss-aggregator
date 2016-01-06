<?php

namespace Aventura\Wprss\Core\Licensing\Api;

use \Exception;

/**
 * Exception class, thrown when the Licensing API encounters an error related to the HTTP request or response.
 *
 * @since [*next-version*]
 */
class HttpException extends Exception {

	/**
	 * Constructor.
	 * 
	 * @param string $error The reason why the request failed.
	 */
	public function __construct($reason) {
		parent::__construct( sprintf( 'Licensing API request failed. %1$s', $reason ) );
	}

}
