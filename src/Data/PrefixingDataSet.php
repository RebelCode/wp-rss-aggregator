<?php

namespace RebelCode\Wpra\Core\Data;

/**
 * An implementation of a delegate data set that prefixes consumer keys before delegating to the inner data set.
 *
 * @since [*next-version*]
 */
class PrefixingDataSet extends AbstractDelegateDataSet
{
    /**
     * The prefix to use when using the inner data set.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface $inner  The inner data set.
     * @param string           $prefix The prefix to use when using the inner data set.
     */
    public function __construct(DataSetInterface $inner, $prefix)
    {
        parent::__construct($inner);
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getInnerKey($outerKey)
    {
        return ($this->inner->offsetExists($outerKey))
            ? $outerKey
            : $this->prefix . $outerKey;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getOuterKey($innerKey)
    {
        $prefixLength = strlen($this->prefix);

        return ($prefixLength > 0 && strpos($innerKey, $this->prefix) === 0)
            ? substr($innerKey, $prefixLength)
            : $innerKey;
    }
}
