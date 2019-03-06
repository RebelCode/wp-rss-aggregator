<?php

namespace RebelCode\Wpra\Core\Data;

use Dhii\Di\Exception\NotFoundException;

/**
 * A data set that inherits missing entries from a parent data set.
 *
 * @since [*next-version*]
 */
abstract class AbstractInheritingDataSet implements DataSetInterface
{
    /**
     * The parent data set.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface|null
     */
    protected $parent;

    /**
     * A mapping of input keys to internal keys.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $aliases;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array                 $aliases A mapping of input keys to internal keys.
     * @param DataSetInterface|null $parent  Optional parent data set to inherit from.
     */
    public function __construct($aliases = [], $parent = null)
    {
        $this->aliases = $aliases;
        $this->parent = $parent;
    }

    /**
     * Aliases a key if an alias is set for it.
     *
     * @since [*next-version*]
     *
     * @param string $key The key to alias.
     *
     * @return string The aliased key or the argument-given key if no alias is set for the given key.
     */
    protected function aliasKey($key)
    {
        return (array_key_exists($key, $this->aliases))
            ? $this->aliases[$key]
            : $key;
    }

    /**
     * Reverses an alias back to the key.
     *
     * @since [*next-version*]
     *
     * @param string $alias The alias.
     *
     * @return string The key for the given alias, or the argument if no key was found for the given alias.
     */
    protected function reverseAlias($alias)
    {
        static $cache = null;

        if ($cache === null) {
            $cache = array_flip($this->aliases);
        }

        return (array_key_exists($alias, $cache))
            ? $cache[$alias]
            : $alias;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetGet($key)
    {
        $aKey = $this->aliasKey($key);

        if ($this->has($aKey)) {
            return $this->get($aKey);
        }

        if ($this->parent === null) {
            throw new NotFoundException(sprintf('Entry with key "%s" was not found', $key));
        }

        return $this->parent[$key];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetExists($key)
    {
        $aKey = $this->aliasKey($key);

        return $this->has($aKey) || ($this->parent !== null && isset($this->parent[$key]));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetSet($key, $value)
    {
        $aKey = $this->aliasKey($key);

        $this->set($aKey, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetUnset($key)
    {
        $aKey = $this->aliasKey($key);

        $this->delete($aKey);
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
