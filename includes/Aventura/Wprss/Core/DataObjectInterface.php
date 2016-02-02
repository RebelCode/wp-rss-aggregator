<?php

namespace Aventura\Wprss\Core;

/**
 * An interface for something that can hold and manipulate arbitrary internal data.
 *
 * @since [*next-version*]
 */
interface DataObjectInterface
{
    /**
     * @since [*next-version*]
     */
    public function getData($key = null);

    /**
     * @since [*next-version*]
     */
    public function hasData($key = null);

    /**
     * @since [*next-version*]
     */
    public function unsetData($key = null);

    /**
     * @since [*next-version*]
     */
    public function setData($key, $value = null);

    /**
     * @since [*next-version*]
     */
    public function setDataUsingMethod($key);
}