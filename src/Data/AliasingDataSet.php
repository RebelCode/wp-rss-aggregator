<?php

namespace RebelCode\Wpra\Core\Data;

/**
 * A data set that wraps around another data set to alias its keys.
 *
 * The input keys given to this implementation's offset access methods are aliased and forwarded to the inner data set.
 * During iteration, the yielded keys are also aliases. This allows the consumer of this data set to separate
 * themselves completely from the keys of the inner data set.
 *
 * @since 4.13
 */
class AliasingDataSet extends AbstractDelegateDataSet
{
    /**
     * A mapping of input keys to internal keys.
     *
     * @since 4.13
     *
     * @var array
     */
    protected $aliasToKeyMap;

    /**
     * A flipped mapping of the aliases map.
     *
     * @since 4.13
     *
     * @var array
     */
    protected $keyToAliasMap;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param DataSetInterface $dataset The inner data set.
     * @param array            $aliases Optional mapping of input keys to inner data set keys.
     */
    public function __construct(DataSetInterface $dataset, array $aliases)
    {
        parent::__construct($dataset);
        $this->aliasToKeyMap = $aliases;
        $this->keyToAliasMap = array_flip($aliases);
    }

    /**
     * {@inheritdoc}
     *
     * Resolves an alias to retrieve the actual key to be used with the inner data set.
     *
     * @since 4.13
     */
    protected function getInnerKey($alias)
    {
        return (array_key_exists($alias, $this->aliasToKeyMap))
            ? $this->aliasToKeyMap[$alias]
            : $alias;
    }

    /**
     * {@inheritdoc}
     *
     * Aliases a key to retrieve the key that consumers expect.
     *
     * @since 4.13
     */
    protected function getOuterKey($key)
    {
        return (array_key_exists($key, $this->keyToAliasMap))
            ? $this->keyToAliasMap[$key]
            : $key;
    }
}
