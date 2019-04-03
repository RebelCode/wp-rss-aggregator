<?php

namespace RebelCode\Wpra\Core\Query;

/**
 * A simple implementation of a WordPress query iterator.
 *
 * @since [*next-version*]
 */
class WpQueryIterator extends AbstractWpQueryIterator
{
    /**
     * The WP Query arguments.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $args;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array $args The WP Query arguments.
     */
    public function __construct($args)
    {
        $this->args = $args;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getQueryArgs()
    {
        return $this->args;
    }
}
