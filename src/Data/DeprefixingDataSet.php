<?php

namespace RebelCode\Wpra\Core\Data;

/**
 * An implementation of a delegate data set that removes prefixes from consumer keys before delegating to the inner data
 * set.
 *
 * @since 4.13
 */
class DeprefixingDataSet extends AbstractDelegateDataSet
{
    /**
     * The prefix to strip from the inner data set's keys.
     *
     * @since 4.13
     *
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param DataSetInterface $inner  The inner data set.
     * @param string           $prefix The prefix to strip from the inner data set's keys.
     */
    public function __construct(DataSetInterface $inner, $prefix)
    {
        parent::__construct($inner);
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getInnerKey($outerKey)
    {
        $prefixLength = strlen($this->prefix);

        return ($prefixLength > 0 && strpos($outerKey, $this->prefix) === 0)
            ? substr($outerKey, $prefixLength)
            : $outerKey;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getOuterKey($innerKey)
    {
        return $this->prefix . $innerKey;
    }
}
