<?php

namespace Aventura\Wprss\Core\Model\Collection;

/**
 * Common functionality for item collection items.
 *
 * @since [*next-version*]
 */
abstract class AbstractItemCollectionItem implements ItemCollectionItemInterface
{
    protected $key;
    protected $value;
    protected $isHit;

    /**
     * {@inheritdoc}
     * 
     * @since [*next-version*]
     */
    public function get()
    {
        return $this->_get();
    }

    /**
     * Low-level retrieval of the item value.
     *
     * @since [*next-version*]
     * @return mixed The value of this item.
     */
    protected function _get()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Low-level setting of the key.
     *
     * Key should be immutable for the outside.
     *
     * @since [*next-version*]
     * @param string $key The key to set.
     * @return AbstractItemCollectionItem This instance.
     */
    protected function _setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function isHit()
    {
        return $this->isHit;
    }

    /**
     * Set whether or not the retrieval of this item was a hit.
     *
     * @since [*next-version*]
     * @param bool $isHit Whether or not the retrieval of this item will be considered to be a hit.
     * @return AbstractItemCollectionItem This instance.
     */
    protected function _setIsHit($isHit)
    {
        $this->isHit = (bool) $isHit;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function set($value)
    {
        $this->_set($value);
        
        return $this;
    }

    /**
     * Low-level value setting.
     *
     * @since [*next-version*]
     * @param mixed $value The value for this item.
     * @return AbstractItemCollectionItem This instance.
     */
    protected function _set($value)
    {
        $this->value = $value;

        return $this;
    }
}