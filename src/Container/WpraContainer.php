<?php

namespace RebelCode\Wpra\Core\Container;

/**
 * A container implementation specific to WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class WpraContainer extends ModuleContainer
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createInnerContainer(array $definitions)
    {
        return new WpFilterContainer(parent::createInnerContainer($definitions));
    }
}
