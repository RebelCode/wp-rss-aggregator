<?php

namespace RebelCode\Wpra\Core\Data;

use RebelCode\Wpra\Core\Util\MergedIterator;

/**
 * An implementation of a data set that merged two data sets, with customizable override behavior and iteration.
 *
 * @since [*next-version*]
 */
class MergedDataSet extends AbstractDataSet
{
    /**
     * Iteration mode for only iterating over the primary data set.
     *
     * @since [*next-version*]
     */
    const ITERATE_PRIMARY = 0;

    /**
     * Iteration mode for only iterating over the secondary data set.
     *
     * @since [*next-version*]
     */
    const ITERATE_SECONDARY = 1;

    /**
     * Iteration mode for iterating over both data sets, yielding only unique keys.
     *
     * @since [*next-version*]
     */
    const ITERATE_BOTH = 2;

    /**
     * The primary data set.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $primary;

    /**
     * The secondary data set.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $secondary;

    /**
     * The map of keys that the primary overrides.
     *
     * @since [*next-version*]
     *
     * @var bool[]
     */
    protected $overrideMap;

    /**
     * Description
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $iteration;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface $primary     The primary data set to use.
     * @param DataSetInterface $secondary   The secondary data set to use for keys that don't exist in the primary.
     * @param bool[]           $overrideMap The map of keys to booleans. If the primary data set has the key but the
     *                                      key exists in this map and is mapped to `true`, the secondary dataset will
     *                                      be used instead. If the key is not in the map or maps to `false`, the normal
     *                                      override behavior is used.
     * @param int              $iteration   The iteration mode. See the `ITERATE_*` constants provided by this class.
     */
    public function __construct(
        DataSetInterface $primary,
        DataSetInterface $secondary,
        $overrideMap = [],
        $iteration = self::ITERATE_BOTH
    ) {
        $this->primary = $primary;
        $this->secondary = $secondary;
        $this->overrideMap = $overrideMap;
        $this->iteration = $iteration;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        return $this->primary->offsetExists($key) || $this->secondary->offsetExists($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function get($key)
    {
        return $this->getChildForKey($key)->offsetGet($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        $this->primary->offsetSet($key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function delete($key)
    {
        $this->primary->offsetUnset($key);
    }

    /**
     * Checks if a key is marked as being overridden.
     *
     * @since [*next-version*]
     *
     * @param int|string $key The key.
     *
     * @return bool True if the key is overridden, false if not.
     */
    protected function isOverridden($key)
    {
        return array_key_exists($key, $this->overrideMap) && $this->overrideMap[$key];
    }

    /**
     * Retrieves the child data set to use for a given key.
     *
     * @since [*next-version*]
     *
     * @param int|string $key The key.
     *
     * @return DataSetInterface The data set to use.
     */
    protected function getChildForKey($key)
    {
        if ($this->primary->offsetExists($key) && !$this->isOverridden($key)) {
            return $this->primary;
        }

        return $this->secondary;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        if ($this->iteration === static::ITERATE_PRIMARY) {
            return $this->primary;
        }

        if ($this->iteration === static::ITERATE_SECONDARY) {
            return $this->secondary;
        }

        return new MergedIterator([$this->primary, $this->secondary]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function current()
    {
        $key = $this->_iterator->key();
        $set = $this->getChildForKey($key);

        return $set->offsetGet($key);
    }
}
