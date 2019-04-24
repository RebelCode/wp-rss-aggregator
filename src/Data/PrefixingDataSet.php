<?php

namespace RebelCode\Wpra\Core\Data;

/**
 * An implementation of a delegate data set that prefixes consumer keys before delegating to the inner data set.
 *
 * @since 4.13
 */
class PrefixingDataSet extends AbstractDelegateDataSet
{
    /**
     * The prefix to use when using the inner data set.
     *
     * @since 4.13
     *
     * @var string
     */
    protected $prefix;

    /**
     * Whether or not un-prefixed keys in the inner dataset can be accessed.
     *
     * @since 4.13
     *
     * @var bool
     */
    protected $strict;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param DataSetInterface $inner  The inner data set.
     * @param string           $prefix The prefix to use when using the inner data set.
     * @param bool             $strict True to allow access to not un-prefixed keys in the inner dataset, false to
     *                                 hide them completely.
     */
    public function __construct(DataSetInterface $inner, $prefix, $strict = false)
    {
        parent::__construct($inner);
        $this->prefix = $prefix;
        $this->strict = $strict;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getInnerKey($outerKey)
    {
        return ($this->inner->offsetExists($outerKey) && !$this->strict)
            ? $outerKey
            : $this->prefix . $outerKey;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getOuterKey($innerKey)
    {
        $prefixLength = strlen($this->prefix);

        return ($prefixLength > 0 && strpos($innerKey, $this->prefix) === 0)
            ? substr($innerKey, $prefixLength)
            : $innerKey;
    }
}
