<?php

namespace Aventura\Wprss\Core\Template\Api;

use WP_Query;

/**
 * An interface for an object that can parse an array of arguments to create a template render context.
 *
 * The template render context is an array of data that is passed to the template during rendering. The template will
 * have access to this data and can output it or use it for conditional rendering.
 *
 * The purpose of objects that implement this interface is to take some array of arguments and use it to generate the
 * render context. The arguments may come from any source, be it shortcode parameters, a page builder block's
 * options, or a theme function's arguments.
 *
 * @since [*next-version*]
 */
interface TemplateContextFactoryInterface
{
    /**
     * Creates the template context for a given feed item query and a set of configuration arguments.
     *
     * @since [*next-version*]
     *
     * @param WP_Query $query  The feed items query.
     * @param array    $config Optional set of configuration, typically some set of saved settings, consumer-provided
     *                         configuration or a mix of both.
     *
     * @return array The built render context.
     */
    public function make(WP_Query $query, array $config = []);
}
