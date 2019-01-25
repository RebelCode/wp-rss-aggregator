<?php

namespace Aventura\Wprss\Core\Template\Api;

use WP_Post;

/**
 * An interface for an object that can convert a feed item into a a template context.
 *
 * @since [*next-version*]
 */
interface FeedItemContextFactoryInterface
{
    /**
     * Creates the render context for a single feed item.
     *
     * @since [*next-version*]
     *
     * @param WP_Post $item   The feed item post instance.
     * @param array   $config Optional configuration, typically some set of saved settings, the consumer template's
     *                        current render context, or a mix of both.
     *
     * @return array The feed item's data, as a render context.
     */
    public function make(WP_Post $item, array $config = []);
}
