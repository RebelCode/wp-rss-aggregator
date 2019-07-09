<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayAccess;
use ArrayIterator;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use stdClass;
use Traversable;

/**
 * A data set implementation that uses a static array or object data store.
 *
 * @since 4.13
 */
class ArrayDataSet extends AbstractDataSet
{
    /* @since 4.13 */
    use NormalizeArrayCapableTrait;

    /* @since 4.13 */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since 4.13 */
    use StringTranslatingTrait;

    /**
     * The options data as an associative array.
     *
     * @since 4.13
     *
     * @var array
     */
    protected $data;

    /**
     * @since 4.14
     *
     * @var bool
     */
    protected $recursive;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param array|stdClass|Traversable $data      The data store, as an associative array, object or iterator.
     * @param bool                       $recursive Whether or not to recursively set data to children data sets.
     */
    public function __construct($data, $recursive = false)
    {
        $this->data = $this->_normalizeArray($data);
        $this->recursive = $recursive;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function get($key)
    {
        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function set($key, $value)
    {
        if ($this->recursive && (is_array($value) || $value instanceof ArrayAccess)) {
            foreach ($value as $subKey => $subValue) {
                $this->data[$key][$subKey] = $subValue;
            }

            return;
        }

        $this->data[$key] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function delete($key)
    {
        unset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getIterator()
    {
        return new ArrayIterator($this->data);
    }
}
