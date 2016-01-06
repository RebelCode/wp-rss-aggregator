<?php

namespace Aventura\Wprss\Core\Licensing\Plugin\Updater;

use \Exception;

/**
 * Exception class, thrown when an updater instance is attempted to be instantiated using the classname of a class that is not a valid UpdaterInstance class.
 *
 * @see Aventura\Wprss\Core\Licensing\Plugin\UpdaterInterface
 * @since [*next-version*]
 */
class InstanceException extends Exception {

	/**
	 * Constructor.
	 * 
	 * @param string $instanceClassname The class name of the instance.
	 */
	public function __construct($instanceClassname) {
		parent::__construct( sprintf('Class "%1$s" does not implement UpdaterInterface', $instanceClassname) );
	}

}
