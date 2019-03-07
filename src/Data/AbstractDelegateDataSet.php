<?php

namespace RebelCode\Wpra\Core\Data;

/**
 * Abstract implementation of a data set that delegates to an inner data set.
 *
 * @since [*next-version*]
 */
abstract class AbstractDelegateDataSet extends AbstractDataSet
{
    /**
     * The inner data set instance.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $inner;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface $inner The inner data set.
     */
    public function __construct(DataSetInterface $inner)
    {
        $this->inner = $inner;
    }

    /**
     * Retrieves the inner key to use with the inner data set for a given outer data set key.
     *
     * @since [*next-version*]
     *
     * @param int|string $outerKey The outer data set key.
     *
     * @return int|string The inner data set key.
     */
    protected function getInnerKey($outerKey)
    {
        return $outerKey;
    }

    /**
     * Retrieves the inner key to use with the inner data set for a given outer data set key.
     *
     * @since [*next-version*]
     *
     * @param int|string $innerKey The inner data set key.
     *
     * @return int|string The outer data set key.
     */
    protected function getOuterKey($innerKey)
    {
        return $innerKey;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function get($key)
    {
        return $this->inner->offsetGet($this->getInnerKey($key));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        return $this->inner->offsetExists($this->getInnerKey($key));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        $this->inner->offsetSet($this->getInnerKey($key), $value);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function delete($key)
    {
        $this->inner->offsetUnset($this->getInnerKey($key));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        return $this->inner;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function key()
    {
        return $this->getOuterKey($this->inner->key());
    }
}
