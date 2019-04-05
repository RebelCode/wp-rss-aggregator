<?php

namespace RebelCode\Wpra\Core\Data\Collections;

/**
 * An implementation of a null collection.
 *
 * @since [*next-version*]
 */
class NullCollection implements CollectionInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetGet($offset)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetExists($offset)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function filter($filters)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getCount()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function clear()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function current()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function next()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]]
     */
    public function key()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function valid()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
    }
}
