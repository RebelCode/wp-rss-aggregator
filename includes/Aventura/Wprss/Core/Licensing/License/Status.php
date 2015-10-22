<?php

namespace Aventura\Wprss\Core\Licensing\License;

/**
 * Enum-style abstract class for license statuses.
 */
abstract class Status {
	const VALID		=	'valid';
	const INVALID	=	'invalid';
	const INACTIVE	=	'inactive';
	const EXPIRED	=	'expired';
}
