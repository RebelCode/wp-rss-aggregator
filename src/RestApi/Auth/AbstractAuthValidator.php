<?php

namespace RebelCode\Wpra\Core\RestApi\Auth;

use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Validation\AbstractValidatorBase;

/**
 * Abstract functionality for authorization validators.
 *
 * @since [*next-version*]
 */
abstract class AbstractAuthValidator extends AbstractValidatorBase
{
    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;
}
