<?php

namespace RebelCode\Wpra\Core\Entities\Properties;

use OutOfBoundsException;
use RebelCode\Entities\Api\EntityInterface;
use RebelCode\Entities\Api\PropertyInterface;

/**
 * A specialized feed item property implementation that falls back to a source property.
 *
 * This property replicates the get behavior of either another feed item property of a property of the feed source.
 * Which one is replicated depends on the value of a boolean "control" property. When the control property has a value
 * of true, the feed item property is used. If the control property has a value of false, or true but the feed item has
 * no value for the property, the feed source property is used instead. All set operations will be handled using the
 * feed item property.
 *
 * @since [*next-version*]
 */
class WpraItemSourceProperty implements PropertyInterface
{
    /**
     * @since [*next-version*]
     *
     * @var PropertyInterface
     */
    protected $itemProp;

    /**
     * @since [*next-version*]
     *
     * @var PropertyInterface
     */
    protected $sourceProp;

    /**
     * @since [*next-version*]
     *
     * @var PropertyInterface
     */
    protected $controlProp;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PropertyInterface $itemProp The feed item property.
     * @param PropertyInterface $sourceProp The feed source property.
     * @param PropertyInterface $controlProp The control property.
     */
    public function __construct(
        PropertyInterface $itemProp,
        PropertyInterface $sourceProp,
        PropertyInterface $controlProp
    ) {
        $this->itemProp = $itemProp;
        $this->sourceProp = $sourceProp;
        $this->controlProp = $controlProp;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getValue(EntityInterface $item)
    {
        $feed = $item->get('source');

        try {
            $value = $this->controlProp->getValue($item)
                ? $this->itemProp->getValue($item)
                : null;

            if (!empty($value)) {
                return $value;
            }
        } catch (OutOfBoundsException $exception) {
            // Do nothing
        }

        return $this->sourceProp->getValue($feed);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function setValue(EntityInterface $entity, $value)
    {
        return $this->itemProp->setValue($entity, $value);
    }
}
