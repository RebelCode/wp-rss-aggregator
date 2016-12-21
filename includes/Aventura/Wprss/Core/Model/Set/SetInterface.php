<?php

namespace Aventura\Wprss\Core\Model\Set;

use Dhii\Collection;

/**
 * Something that behaves like a set.
 *
 * @since [*next-version*]
 */
interface SetInterface extends Collection\SetInterface
{
    /**
     * Determines whether this instance contains at least one of the items in the given list.
     *
     * @since [*next-version*]
     *
     * @param mixed[]|\Traversable $items The list of items to check for.
     */
    public function hasOneOf($items);
}
