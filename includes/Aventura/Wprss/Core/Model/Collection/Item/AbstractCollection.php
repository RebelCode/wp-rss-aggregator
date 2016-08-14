<?php

namespace Aventura\Wprss\Core\Model\Collection\Item;

/**
 * Base functionality for item-object-based collections.
 *
 * @since [*next-version*]
 */
abstract class AbstractCollection implements CollectionInterface
{
    protected $values = array();

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function clear()
    {
        return $this->_clearValues();
    }

    /**
     * Low-level removal of all items.
     *
     * @since [*next-version*]
     * @return bool True if items cleared; false otherwise.
     */
    protected function _clearValues()
    {
        $this->values = array();

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function deleteItem($key)
    {
        return $this->_deleteItem($key);
    }

    /**
     * Low level item removal function.
     *
     * @since [*next-version*]
     * @param string $key The key of the item to delete.
     * @return bool True if item successfully deleted; false otherwise.
     */
    protected function _deleteItem($key)
    {
        if (!isset($this->values[$key])) {
            return false;
        }
        
        unset($this->values[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $_key) {
            if (!$this->_deleteItem($_key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getItem($key)
    {
        $value = $this->_getValue($key);
        $isHit = $this->hasItem($key);
        $item = $this->_createItem($key, $value, $isHit);

        return $item;
    }

    /**
     * Get raw item.
     *
     * @since [*next-version*]
     * @param string $key The key of the value to get.
     * @param mixed $default What to return if the key is not found.
     * @return mixed The value for the specified key.
     */
    protected function _getValue($key, $default = null)
    {
        if (!isset($this->values[$key])) {
            return $default;
        }

        return $this->values[$key];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getItems(array $keys = array())
    {
        if (empty($keys)) {
            $keys = array_keys($this->values);
        }

        $items = array();
        foreach ($keys as $_key) {
            $item = $this->getItem($_key);
            $items[$item->getKey()] = $item;
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function hasItem($key)
    {
        return isset($this->values[$key]);
    }

    /**
     * Creates a new item instance.
     *
     * @since [*next-version*]
     * @param string $key The key to give to this item.
     * @param mixed|ItemCollectionItemInterface|null $value The value for the new item, if any.
     * @param boolean $isHit Whether or not the value corresponding to this item's key exists in this collection.
     * @return AbstractItemCollectionItem
     */
    abstract protected function _createItem($key, $value = null, $isHit = true);
}
