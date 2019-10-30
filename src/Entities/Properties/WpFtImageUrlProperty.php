<?php

namespace RebelCode\Wpra\Core\Entities\Properties;

use RebelCode\Entities\Api\EntityInterface;
use RebelCode\Entities\Api\PropertyInterface;

/**
 * An implementation of a property that transforms a WordPress featured image ID into a URL when reading, and
 * vice-versa when writing.
 *
 * @since 4.16
 */
class WpFtImageUrlProperty implements PropertyInterface
{
    /**
     * @since 4.16
     *
     * @var string
     */
    protected $ftImageIdKey;

    /**
     * Constructor.
     *
     * @since 4.16
     *
     * @param string $ftImageIdKey The data store key where the featured image ID is stored.
     */
    public function __construct($ftImageIdKey)
    {
        $this->ftImageIdKey = $ftImageIdKey;
    }

    /**
     * @inheritdoc
     *
     * @since 4.16
     */
    public function getValue(EntityInterface $entity)
    {
        $ftImageId = $entity->getStore()->get($this->ftImageIdKey);
        $ftImageUrl = wp_get_attachment_image_url($ftImageId, '');

        return $ftImageUrl;
    }

    /**
     * @inheritdoc
     *
     * @since 4.16
     */
    public function setValue(EntityInterface $entity, $value)
    {
        $id = wpra_get_attachment_id_from_url($value);

        if (is_numeric($id) && $id > 0) {
            return [$this->ftImageIdKey => $id];
        }

        return [];
    }
}
