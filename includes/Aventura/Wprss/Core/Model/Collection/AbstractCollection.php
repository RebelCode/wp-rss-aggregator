<?php

namespace Aventura\Wprss\Core\Model\Collection;

use Dhii\Collection\AbstractSearchableCollection;

/**
 * Common functionality for all collections.
 *
 * @since [*next-version*]
 */
abstract class AbstractCollection extends AbstractSearchableCollection
{
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    protected function _construct()
    {
        $this->_clearItems();
        $this->_clearItemCache();
    }

    /**
     * Returns the item set of this instance to its initial state.
     *
     * @since [*next-version*]
     */
    protected function _clearItems()
    {
        $this->items = array();
    }
}
