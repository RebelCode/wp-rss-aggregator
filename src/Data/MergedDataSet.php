<?php

namespace RebelCode\Wpra\Core\Data;

use Exception;
use RebelCode\Wpra\Core\Util\MergedIterator;

/**
 * An implementation of a data set that merged two data sets, with customizable override behavior and iteration.
 *
 * @since 4.13
 */
class MergedDataSet extends AbstractDataSet
{
    /**
     * Iteration mode for only iterating over the primary data set.
     *
     * @since 4.13
     */
    const ITERATE_PRIMARY = 0;

    /**
     * Iteration mode for only iterating over the secondary data set.
     *
     * @since 4.13
     */
    const ITERATE_SECONDARY = 1;

    /**
     * Iteration mode for iterating over both data sets, yielding only unique keys.
     *
     * @since 4.13
     */
    const ITERATE_BOTH = 2;

    /**
     * The primary data set.
     *
     * @since 4.13
     *
     * @var DataSetInterface
     */
    protected $primary;

    /**
     * The secondary data set.
     *
     * @since 4.13
     *
     * @var DataSetInterface
     */
    protected $secondary;

    /**
     * The map of keys that the primary overrides.
     *
     * @since 4.13
     *
     * @var bool[]
     */
    protected $overrideMap;

    /**
     * Description
     *
     * @since 4.13
     *
     * @var bool
     */
    protected $iteration;

    /**
     * Constructor.
     *
     * @since 4.13
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
     * @since 4.13
     */
    protected function has($key)
    {
        return $this->isOverridden($key)
            ? $this->secondary->offsetExists($key)
            : $this->primary->offsetExists($key) || $this->secondary->offsetExists($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function get($key)
    {
        return $this->primary->offsetExists($key) && !$this->isOverridden($key)
            ? $this->primary->offsetGet($key)
            : $this->secondary->offsetGet($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function set($key, $value)
    {
        if ($this->isOverridden($key)) {
            $this->secondary->offsetSet($key, $value);

            return;
        }

        try {
            $this->primary->offsetSet($key, $value);
        } catch (Exception $exception) {
            $this->secondary->offsetSet($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function delete($key)
    {
        if ($this->isOverridden($key)) {
            $this->secondary->offsetUnset($key);

            return;
        }

        try {
            $this->primary->offsetUnset($key);
        } catch (Exception $exception) {
            $this->secondary->offsetUnset($key);
        }
    }

    /**
     * Checks if a key is marked as being overridden.
     *
     * @since 4.13
     *
     * @param int|string $key The key.
     *
     * @return bool True if the key is overridden, false if not.
     */
    protected function isOverridden($key)
    {
        return array_key_exists($key, $this->overrideMap) && $this->overrideMap[$key] !== false;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
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
     * @since 4.13
     */
    public function current()
    {
        $key = $this->_iterator->key();
        $set = $this->getChildForKey($key);
        $val = $set->offsetGet($key);

        return $this->yieldIterationValue($val);
    }

    /**
     * Retrieves the child dataset to use for a given key.
     *
     * This method will attempt to return the dataset that already has the key. However, priority is determined by
     * referring to the override map.
     *
     * If a key is marked as overwritten, then the secondary dataset is prioritized. If it does not contain the
     * key, the primary dataset is returned.
     *
     * If a key is not marked as overwritten, then the primary dataset is prioritized. If it does not contain the key,
     * then the secondary dataset is returned.
     *
     * @since 4.13
     *
     * @param int|string $key The key.
     *
     * @return DataSetInterface The data set to use.
     */
    protected function getChildForKey($key)
    {
        if ($this->isOverridden($key)) {
            return $this->secondary->offsetExists($key)
                ? $this->secondary
                : $this->primary;
        }

        return $this->primary->offsetExists($key)
            ? $this->primary
            : $this->secondary;
    }
}
