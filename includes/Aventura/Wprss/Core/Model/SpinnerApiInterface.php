<?php

namespace Aventura\Wprss\Core\Model;

/**
 * Something that can spin content, usually using a remote service.
 *
 * @since [*next-version*]
 */
interface SpinnerApiInterface
{
    /**
     * Spins the content using the remote API.
     *
     * @since [*next-version*]
     * @param mixed $content The content to spin.
     * @param array $options Options for spinning.
     */
    public function spin($content, $options = array());
}