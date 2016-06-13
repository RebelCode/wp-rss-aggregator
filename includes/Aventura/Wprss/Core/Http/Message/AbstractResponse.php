<?php

namespace Aventura\Wprss\Core\Http\Message;

use Aventura\Wprss\Core;

/**
 * Base functionality for responses
 * 
 * @since [*next-version*]
 */
abstract class AbstractResponse extends Core\DataObject implements ResponseInterface
{
    /**
     * @since [*next-version*]
     */
    const K_BODY = 'body';
    
    /**
     * @since [*next-version*]
     */
    protected $body;
    
    /**
     * {@inheritdoc}
     * @since [*next-version*]
     */
    public function getBody()
    {
        return $this->getData(static::K_BODY);
    }
}
