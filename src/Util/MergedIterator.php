<?php

namespace RebelCode\Wpra\Core\Util;

use AppendIterator;
use stdClass;
use Traversable;

/**
 * An implementation of an iterator that iterates over several iterators in sequence, without yielding duplicate keys.
 *
 * Once a key and its value have been yielded, no further values for the same keys are yielded. In other words, the
 * precedence for iterators is "first come, first serve".
 *
 * @since [*next-version*]
 */
class MergedIterator extends AppendIterator
{
    /**
     * Temporary list of keys yielded during iteration, used to avoid yielded duplicates.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $keys;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $iterators The iterators.
     */
    public function __construct($iterators = [])
    {
        parent::__construct();

        foreach ($iterators as $iterator) {
            $iterator->rewind();
            $this->append($iterator);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        parent::rewind();

        $this->keys = [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function next()
    {
        do {
            parent::next();
            $nextKey = $this->key();
        } while ($this->valid() && isset($this->keys[$nextKey]));

        $this->keys[$nextKey] = 1;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function current()
    {
        $this->keys[$this->key()] = 1;

        return parent::current();
    }
}
