<?php

namespace Aventura\Wprss\Core\Template\Api;

use WP_Query;

/**
 * An interface for objects that can create feed item {@link WP_Query} instances to be used in templates.
 *
 * @since [*next-version*]
 */
interface TemplateQueryFactoryInterface
{
    /**
     * Creates a WordPress query object.
     *
     * @since [*next-version*]
     *
     * @param array $args An array of arguments.
     *
     * @return WP_Query
     */
    public function make(array $args = []);
}
