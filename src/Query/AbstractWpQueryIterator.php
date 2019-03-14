<?php

namespace RebelCode\Wpra\Core\Query;

use Iterator;
use WP_Post;

/**
 * Abstract implementation of a WP Query iterator.
 *
 * @since [*next-version*]
 */
abstract class AbstractWpQueryIterator implements Iterator
{
    /**
     * The queried posts.
     *
     * @since [*next-version*]
     *
     * @var WP_Post[]
     */
    protected $posts;

    /**
     * Retrieves the WordPress query args.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    abstract protected function getQueryArgs();

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        $this->posts = get_posts($this->getQueryArgs());
        reset($this->posts);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function next()
    {
        next($this->posts);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function key()
    {
        return key($this->posts);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function current()
    {
        return current($this->posts);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function valid()
    {
        return key($this->posts) !== null;
    }
}
