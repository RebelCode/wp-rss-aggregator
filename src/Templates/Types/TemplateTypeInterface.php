<?php

namespace RebelCode\Wpra\Core\Templates\Types;

use Dhii\Output\TemplateInterface;

/**
 * Interface for objects that represent WP RSS Aggregator template types.
 *
 * @since [*next-version*]
 */
interface TemplateTypeInterface extends TemplateInterface
{
    /**
     * Retrieves the template type key.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    public function getKey();

    /**
     * Retrieves the template type name.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    public function getName();
}
