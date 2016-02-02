<?php

namespace Aventura\Wprss\Core\Model\Event;

/**
 * @since [*next-version*]
 */
interface EventInterface
{
    /**
     * Get the event name.
     * 
     * @since [*next-version*]
     */
    public function getName();

    /**
     * Get event data.
     *
     * @since [*next-version*]
     * @param string|null $key All event data, or data at a specific index.\
     */
    public function getData($key = null);

    /**
     * Set event data.
     *
     * @since [*next-version*]
     * @param array|string $key The key to set the value for, or an array of data to replace.
     * @param mixed|null $value The value to set for the data key.
     */
    public function setData($key, $value = null);
}