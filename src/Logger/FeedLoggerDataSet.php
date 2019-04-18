<?php

namespace RebelCode\Wpra\Core\Logger;

use ArrayIterator;
use Psr\Log\LoggerInterface;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Util\IteratorDelegateTrait;

/**
 * A data set that contains, and creates on-demand, the logger instances for WP RSS Aggregator feed sources.
 *
 * @since [*next-version*]
 */
class FeedLoggerDataSet implements DataSetInterface
{
    /* @since [*next-version*] */
    use IteratorDelegateTrait;

    /**
     * The logger instances.
     *
     * @since [*next-version*]
     *
     * @var LoggerInterface[]
     */
    protected $instances;

    /**
     * A callable that should accept a feed source ID and return a logger instance.
     *
     * @since [*next-version*]
     *
     * @var callable
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param callable $factory A callable that should accept a feed source ID and return a logger instance.
     */
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
        $this->instances = [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetGet($feedId)
    {
        if (!$this->offsetExists($feedId)) {
            $this->instances[$feedId] = call_user_func_array($this->factory, [$feedId]);
        }

        return $this->instances[$feedId];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetExists($feedId)
    {
        return isset($this->instances[$feedId]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetSet($feedId, $instance)
    {
        $this->instances[$feedId] = $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetUnset($feedId)
    {
        unset($this->instances[$feedId]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        return new ArrayIterator($this->instances);
    }
}
