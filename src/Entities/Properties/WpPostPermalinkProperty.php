<?php

namespace RebelCode\Wpra\Core\Entities\Properties;

use RebelCode\Entities\Api\EntityInterface;
use RebelCode\Entities\Api\PropertyInterface;

/**
 * A property for WordPress post permalinks. Read-only.
 *
 * @since [*next-version*]
 */
class WpPostPermalinkProperty implements PropertyInterface
{
    /**
     * @since [*next-version*]
     *
     * @var PropertyInterface
     */
    protected $idProp;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PropertyInterface $idProp The property for the WP Post instance or ID.
     */
    public function __construct(PropertyInterface $idProp)
    {
        $this->idProp = $idProp;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getValue(EntityInterface $entity)
    {
        return get_post_permalink($this->idProp->getValue($entity));
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function setValue(EntityInterface $entity, $value)
    {
        return [];
    }
}
