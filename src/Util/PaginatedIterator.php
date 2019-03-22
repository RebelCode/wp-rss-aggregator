<?php

namespace RebelCode\Wpra\Core\Util;

use Iterator;
use LimitIterator;

/**
 * A special iterator implementation that paginates another iterator by only iterating over a given page subset.
 *
 * @since [*next-version*]
 */
class PaginatedIterator extends LimitIterator
{
    /**
     * The number of keys that have been yielded during an iteration.
     *
     * @since [*next-version*]
     */
    protected $keyCount;

    /**
     * Whether or not to preserve keys.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $preserveKeys;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Iterator $iterator     The inner iterator.
     * @param int      $page         The page number.
     * @param int      $num          The number of items per page.
     * @param bool     $preserveKeys Whether or not to preserve keys.
     */
    public function __construct(Iterator $iterator, $page, $num, $preserveKeys = false)
    {
        $num = max(1, $num);
        $page = max(1, $page);
        $offset = $num * ($page - 1);
        parent::__construct($iterator, $offset, $num);

        $this->preserveKeys = $preserveKeys;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        parent::rewind();

        $this->keyCount = 0;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function key()
    {
        $key = ($this->preserveKeys)
            ? parent::key()
            : $this->keyCount;

        $this->keyCount++;

        return $key;
    }
}
