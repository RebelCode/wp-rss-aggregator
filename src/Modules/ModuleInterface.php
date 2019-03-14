<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;

/**
 * Interface for WP RSS Aggregator modules.
 *
 * @since [*next-version*]
 */
interface ModuleInterface
{
    /**
     * Retrieves the module's service factories.
     *
     * @since [*next-version*]
     *
     * @return callable[]
     */
    public function getServices();

    /**
     * Runs the module.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c The services container.
     */
    public function run(ContainerInterface $c);
}
