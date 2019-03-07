<?php

namespace RebelCode\Wpra\Core\Util;

use Iterator;

/**
 * Functionality for objects that need to implement the {@link Iterator} interface but wish to delegate the actual
 * iteration to another iterator.
 *
 * @since [*next-version*]
 */
trait IteratorDelegateTrait
{
    /**
     * Internal temporary iterator, used during iteration.
     *
     * @since [*next-version*]
     *
     * @var Iterator|null
     */
    protected $_iterator;

    /**
     * Retrieves the inner iterator to use for iteration.
     *
     * @since [*next-version*]
     *
     * @return Iterator|null
     */
    abstract protected function getIterator();

    /**
     * Rewinds the iterator.
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        $this->_iterator = $this->getIterator();

        if ($this->_iterator !== null) {
            $this->_iterator->rewind();
        }
    }

    /**
     * Advances the iterator onto the next element.
     *
     * @since [*next-version*]
     */
    public function next()
    {
        $this->_iterator->next();
    }

    /**
     * Retrieves the current iteration key.
     *
     * @since [*next-version*]
     *
     * @return mixed
     */
    public function key()
    {
        return $this->_iterator->key();
    }

    /**
     * Retrieves the current iteration value.
     *
     * @since [*next-version*]
     *
     * @return mixed
     */
    public function current()
    {
        return $this->_iterator->current();
    }

    /**
     * Checks if the iterator has more elements to yield.
     *
     * @since [*next-version*]
     *
     * @return bool
     */
    public function valid()
    {
        return $this->_iterator !== null && $this->_iterator->valid();
    }
}
