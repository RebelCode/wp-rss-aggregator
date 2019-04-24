<?php

namespace RebelCode\Wpra\Core\Data\Collections;

/**
 * An implementation of a null collection.
 *
 * @since 4.13
 */
class NullCollection implements CollectionInterface
{
    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function offsetGet($offset)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function offsetExists($offset)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function filter($filters)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function getCount()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function clear()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function current()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function next()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13]
     */
    public function key()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function valid()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function rewind()
    {
    }
}
