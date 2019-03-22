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
     * The current iteration index.
     *
     * @since [*next-version*]
     */
    protected $iterIndex;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Iterator $iterator The inner iterator.
     * @param int      $page     The page number.
     * @param int      $num      The number of items per page.
     */
    public function __construct(Iterator $iterator, $page, $num)
    {
        $num = max(1, $num);
        $page = max(1, $page);
        $offset = $num * ($page - 1);

        parent::__construct($iterator, $offset, $num);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        parent::rewind();

        $this->iterIndex = 0;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function key()
    {
        return $this->iterIndex++;
    }
}
