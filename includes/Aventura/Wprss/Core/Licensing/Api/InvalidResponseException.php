<?php

namespace Aventura\Wprss\Core\Licensing\Api;

use \Exception;

/**
 * Exception class, thrown when the Licensing API responds with an invalid response.
 *
 * @since [*next-version*]
 */
class InvalidResponseException extends Exception {

	/**
	 * Constructor.
	 * 
	 * @param string $reason The reason why the response is invalid.
	 */
	public function __construct($reason) {
		parent::__construct( sprintf( 'Licensing API response is invalid. %1$s', $reason ) );
	}

}
