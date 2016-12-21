<?php

namespace Aventura\Wprss\Core\Model\Set\Synonym;

/**
 * Base functionality of a synonym set.
 *
 * @since [*next-version*]
 */
abstract class AbstractGenericSynonymSet extends AbstractSynonymSet implements SynonymSetInterface
{
    public function __construct(array $items = array())
    {
        parent::__construct($items);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getSynonyms($term)
    {
        return $this->_getSynonyms($term);
    }
}
