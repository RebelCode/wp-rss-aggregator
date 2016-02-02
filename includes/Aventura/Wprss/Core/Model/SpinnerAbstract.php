<?php

namespace Aventura\Wprss\Core\Model;

use Aventura\Wprss\Core;

/**
 * Common functionality for spinners.
 *
 * @since [*next-version*]
 */
abstract class SpinnerAbstract extends Core\Plugin\ComponentAbstract implements SpinnerInterface
{
    public function spin($content, $options = array())
    {
        $this->getApi()->spin($content, $options);
    }

    /**
     * @since [*next-version*]
     * @return SpinnerApiInterface
     */
    abstract public function getApi();
}