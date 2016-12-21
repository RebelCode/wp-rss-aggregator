<?php

namespace Aventura\Wprss\Core\Model\Set;

/**
 * Common functionality for sets that contain other collection descendants.
 *
 * @since [*next-version*]
 */
abstract class AbstractCompositeSet extends AbstractGenericSet
{
    /**
     * Get items from all internal sets.
     *
     * @since [*next-version*]
     *
     * @return mixed[]|\Traversable A list of unique items from all sets in this set.
     */
    abstract protected function _getAllItems();
}