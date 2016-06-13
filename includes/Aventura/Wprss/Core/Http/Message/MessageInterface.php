<?php

namespace Aventura\Wprss\Core\Http\Message;

/**
 * @todo Substitute with PSR-7 interface.
 * @since [*next-version*]
 * @link https://github.com/php-fig/http-message/blob/master/src/MessageInterface.php
 */
interface MessageInterface
{
    /**
     * @since [*next-version*]
     */
    public function getBody();
}
