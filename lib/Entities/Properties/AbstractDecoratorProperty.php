<?php

namespace RebelCode\Entities\Properties;

use OutOfBoundsException;
use RebelCode\Entities\Api\EntityInterface;
use RebelCode\Entities\Api\PropertyInterface;

/**
 * Abstract implementation of a property that decorates another property.
 *
 * @since [*next-version*]
 */
abstract class AbstractDecoratorProperty implements PropertyInterface
{
    /**
     * @since [*next-version*]
     *
     * @var PropertyInterface
     */
    protected $property;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PropertyInterface $property The property instance to decorate.
     */
    public function __construct(PropertyInterface $property)
    {
        $this->property = $property;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getValue(EntityInterface $entity)
    {
        return $this->getter($entity, $this->property->getValue($entity));
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function setValue(EntityInterface $entity, $value)
    {
        return $this->property->setValue($entity, $this->setter($entity, $value));
    }

    /**
     * @since [*next-version*]
     * @return mixed
     */
    abstract protected function getter(EntityInterface $entity, $prev);

    /**
     * @since [*next-version*]
     * @return mixed
     */
    abstract protected function setter(EntityInterface $entity, $value);
}
