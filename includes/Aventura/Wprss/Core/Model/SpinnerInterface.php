<?php

namespace Aventura\Wprss\Core\Model;

/**
 * An interface of something that can spin post data.
 *
 * @since [*next-version*]
 */
interface SpinnerInterface
{
    /**
     *
     * @since [*next-version*]
     * @param string $content
     */
    public function spin($content);
}