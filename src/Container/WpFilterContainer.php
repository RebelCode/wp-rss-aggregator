<?php

namespace RebelCode\Wpra\Core\Container;

use Interop\Container\ContainerInterface;

/**
 * A container implementation that wraps around another container to additionally pass its service results through
 * WordPress filters with hook names equal to the service keys.
 *
 * @since [*next-version*]
 */
class WpFilterContainer implements ContainerInterface
{
    /**
     * The inner container.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $inner;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $inner The inner container.
     */
    public function __construct(ContainerInterface $inner)
    {
        $this->inner = $inner;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($id)
    {
        return apply_filters($id, $this->inner->get($id));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function has($id)
    {
        return $this->inner->has($id);
    }
}
