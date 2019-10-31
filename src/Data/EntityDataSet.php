<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayIterator;
use OutOfBoundsException;
use RebelCode\Entities\Api\EntityInterface;
use RebelCode\Wpra\Core\Util\IteratorDelegateTrait;

/**
 * A data set adapter for entity instances.
 *
 * @since 4.16
 */
class EntityDataSet implements DataSetInterface, EntityInterface
{
    /**
     * @since 4.16
     */
    use IteratorDelegateTrait;

    /**
     * @since 4.16
     *
     * @var EntityInterface
     */
    protected $entity;

    /**
     * Constructor.
     *
     * @since 4.16
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
     * @since 4.16
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
     * @since 4.16
     */
    public function offsetGet($offset)
    {
        return $this->entity->get($offset);
    }

    /**
     * @inheritdoc
     *
     * @since 4.16
     */
    public function offsetSet($offset, $value)
    {
        $this->entity = $this->entity->set([$offset => $value]);
    }

    /**
     * @inheritdoc
     *
     * @since 4.16
     */
    public function offsetUnset($offset)
    {
        $this->entity = $this->entity->set([$offset => '']);
    }

    /**
     * @inheritdoc
     *
     * @since 4.16
     */
    protected function getIterator()
    {
        return new ArrayIterator($this->entity->export());
    }

    /**
     * @inheritdoc
     *
     * @since 4.16
     */
    public function get($key)
    {
        return $this->entity->get($key);
    }

    /**
     * @inheritdoc
     *
     * @since 4.16
     */
    public function set(array $data)
    {
        return $this->entity->set($data);
    }

    /**
     * @inheritdoc
     *
     * @since 4.16
     */
    public function getStore()
    {
        return $this->entity->getStore();
    }

    /**
     * @inheritdoc
     *
     * @since 4.16
     */
    public function getSchema()
    {
        return $this->entity->getSchema();
    }

    /**
     * @inheritdoc
     *
     * @since 4.16
     */
    public function export()
    {
        return $this->entity->export();
    }
}
