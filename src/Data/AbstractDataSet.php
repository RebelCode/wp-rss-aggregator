<?php

namespace RebelCode\Wpra\Core\Data;

use Exception;
use RebelCode\Wpra\Core\Util\IteratorDelegateTrait;
use RuntimeException;

/**
 * A data set that inherits missing entries from a parent data set.
 *
 * @since [*next-version*]
 */
abstract class AbstractDataSet implements DataSetInterface
{
    /* @since [*next-version*] */
    use IteratorDelegateTrait;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetGet($key)
    {
        try {
            if ($this->offsetExists($key)) {
                return $this->get($key);
            }
        } catch (Exception $exception) {
            throw new RuntimeException(
                sprintf('An error occurred while reading the value for the "%s" key', $key), null, $exception
            );
        }

        throw new RuntimeException(sprintf('Entry with key "%s" was not found in the data set', $key));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetExists($key)
    {
        try {
            return $this->has($key);
        } catch (Exception $exception) {
            throw new RuntimeException(
                sprintf('An error occurred while looking up the "%s" key', $key), null, $exception
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetSet($key, $value)
    {
        try {
            $this->set($key, $value);
        } catch (Exception $exception) {
            throw new RuntimeException(
                sprintf('An error occurred while writing to the "%s" key', $key), null, $exception
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetUnset($key)
    {
        try {
            $this->delete($key);
        } catch (Exception $exception) {
            throw new RuntimeException(
                sprintf('An error occurred while deleting the "%s" key', $key), null, $exception
            );
        }
    }

    /**
     * Retrieves a value by key.
     *
     * This method should assume that existence checking has already been performed.
     *
     * @since [*next-version*]
     *
     * @param string $key The key of the value to retrieve.
     *
     * @return mixed The value associated with the given key.
     */
    abstract protected function get($key);

    /**
     * Checks if an entry exists by key.
     *
     * @since [*next-version*]
     *
     * @param string $key The key to check for.
     *
     * @return bool True if the key exists, false if not.
     */
    abstract protected function has($key);

    /**
     * Modifies the value for a given key, creating the entry if it doesn't exist.
     *
     * @since [*next-version*]
     *
     * @param string $key   The key of the entry to modify.
     * @param mixed  $value The new value.
     */
    abstract protected function set($key, $value);

    /**
     * Deletes a specific entry by key.
     *
     * @since [*next-version*]
     *
     * @param string $key The key of the entry to delete.
     */
    abstract protected function delete($key);
}
