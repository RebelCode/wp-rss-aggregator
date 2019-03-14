<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayIterator;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use stdClass;
use Traversable;

/**
 * A data set implementation that uses a static array or object data store.
 *
 * @since [*next-version*]
 */
class ArrayDataSet extends AbstractDataSet
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The options data as an associative array.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $data The data store, as an associative array, object or iterator.
     */
    public function __construct($data)
    {
        $this->data = $this->_normalizeArray($data);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function get($key)
    {
        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function delete($key)
    {
        unset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        return new ArrayIterator($this->data);
    }
}
