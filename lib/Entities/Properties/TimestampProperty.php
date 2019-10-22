<?php

namespace RebelCode\Entities\Properties;

use RebelCode\Entities\Api\EntityInterface;
use RebelCode\Entities\Api\PropertyInterface;

/**
 * A decorator property that translates a datetime string property into a timestamp when reading and writes timestamps
 * as date time strings.
 *
 * @since [*next-version*]
 */
class TimestampProperty extends AbstractDecoratorProperty
{
    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $format;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PropertyInterface $property The property instance to decorate.
     * @param string            $format   The datetime format to use when writing to the data store.
     */
    public function __construct(PropertyInterface $property, $format)
    {
        parent::__construct($property);

        $this->format = $format;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    protected function getter(EntityInterface $entity, $prev)
    {
        return strtotime($prev);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    protected function setter(EntityInterface $entity, $value)
    {
        return gmdate($this->format, $value);
    }
}
