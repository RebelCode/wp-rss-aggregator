<?php

namespace RebelCode\Wpra\Core\Data;

class MaskingDataSet extends AbstractDelegateDataSet
{
    /**
     * An array of keys mapping to booleans, where true exposes the key and false hides the key.
     *
     * @since [*next-version*]
     *
     * @var bool[]
     */
    protected $mask;

    /**
     * The default mask value to use for keys that are not included in the mask array.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $defMask;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface $inner   The inner data set.
     * @param bool[]           $mask    An array of keys mapping to booleans, where true exposes the key and false
     *                                  hides the key.
     * @param bool             $default The default mask value to use for keys that are not included in the mask array.
     */
    public function __construct(DataSetInterface $inner, array $mask, $default = true)
    {
        parent::__construct($inner);

        $this->mask = $mask;
        $this->defMask = $default;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        return $this->isExposed($key) && parent::has($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        parent::rewind();

        $this->seekNextExposed();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function valid()
    {
        $this->seekNextExposed();

        return parent::valid();
    }

    /**
     * Checks if a key is exposed through the mask.
     *
     * @since [*next-version*]
     *
     * @param int|string $key The key to check.
     *
     * @return bool True if the key is exposed, false if the key is hidden.
     */
    protected function isExposed($key)
    {
        return array_key_exists($key, $this->mask)
            ? $this->mask[$key]
            : $this->defMask;
    }

    /**
     * Iterates forward until it an exposed key is found or until the end of iteration is reached.
     *
     * @since [*next-version*]
     */
    protected function seekNextExposed()
    {
        while (parent::valid() && !$this->isExposed($this->key())) {
            parent::next();
        }
    }
}
