<?php

namespace RebelCode\Wpra\Core\Data;

/**
 * Abstract implementation of a data set that delegates to an inner data set.
 *
 * @since 4.13
 */
abstract class AbstractDelegateDataSet extends AbstractDataSet
{
    /**
     * The inner data set instance.
     *
     * @since 4.13
     *
     * @var DataSetInterface
     */
    protected $inner;

    /**
     * Constructor.
     *
     * @since 4.13
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
     * @since 4.13
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
     * @since 4.13
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
     * @since 4.13
     */
    protected function get($key)
    {
        return $this->inner->offsetGet($this->getInnerKey($key));
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function has($key)
    {
        return $this->inner->offsetExists($this->getInnerKey($key));
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function set($key, $value)
    {
        $this->inner->offsetSet($this->getInnerKey($key), $value);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function delete($key)
    {
        $this->inner->offsetUnset($this->getInnerKey($key));
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getIterator()
    {
        return $this->inner;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function key()
    {
        return $this->getOuterKey($this->inner->key());
    }
}
