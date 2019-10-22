<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayIterator;
use OutOfBoundsException;
use RebelCode\Entities\Api\EntityInterface;
use RebelCode\Wpra\Core\Util\IteratorDelegateTrait;

/**
 * A data set adapter for entity instances.
 *
 * @since [*next-version*]
 */
class EntityDataSet implements DataSetInterface, EntityInterface
{
    /**
     * @since [*next-version*]
     */
    use IteratorDelegateTrait;

    /**
     * @since [*next-version*]
     *
     * @var EntityInterface
     */
    protected $entity;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param EntityInterface $entity The entity instance.
     */
    public function __construct(EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function offsetExists($offset)
    {
        try {
            $this->entity->get($offset);

            return true;
        } catch (OutOfBoundsException $exception) {
            return false;
        }
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function offsetGet($offset)
    {
        return $this->entity->get($offset);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function offsetSet($offset, $value)
    {
        $this->entity = $this->entity->set([$offset => $value]);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function offsetUnset($offset)
    {
        $this->entity = $this->entity->set([$offset => '']);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        return new ArrayIterator($this->entity->export());
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function get($key)
    {
        return $this->entity->get($key);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function set(array $data)
    {
        return $this->entity->set($data);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getStore()
    {
        return $this->entity->getStore();
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getSchema()
    {
        return $this->entity->getSchema();
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function export()
    {
        return $this->entity->export();
    }
}
