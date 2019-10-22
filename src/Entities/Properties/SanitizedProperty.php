<?php

namespace RebelCode\Wpra\Core\Entities\Properties;

use RebelCode\Entities\Api\EntityInterface;
use RebelCode\Entities\Api\PropertyInterface;
use RebelCode\Entities\Properties\AbstractDecoratorProperty;
use RebelCode\Wpra\Core\Util\SanitizerInterface;

/**
 * An implementation of a property that decorates another property and uses a sanitizer.
 *
 * @since [*next-version*]
 */
class SanitizedProperty extends AbstractDecoratorProperty
{
    /**
     * @since [*next-version*]
     *
     * @var SanitizerInterface
     */
    protected $sanitizer;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PropertyInterface  $property  The original property instance.
     * @param SanitizerInterface $sanitizer The sanitizer to use after reading and before writing.
     */
    public function __construct(PropertyInterface $property, SanitizerInterface $sanitizer)
    {
        parent::__construct($property);

        $this->sanitizer = $sanitizer;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getter(EntityInterface $entity, $prev)
    {
        return $this->sanitizer->sanitize($prev);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function setter(EntityInterface $entity, $value)
    {
        return $this->sanitizer->sanitize($value);
    }
}
