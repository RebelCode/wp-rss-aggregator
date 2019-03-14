<?php

namespace RebelCode\Wpra\Core;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\ModuleInterface;

/**
 * The WP RSS Aggregator plugin module.
 *
 * @since [*next-version*]
 */
class Plugin implements ModuleInterface
{
    /**
     * The plugin modules.
     *
     * @since [*next-version*]
     *
     * @var ModuleInterface[]
     */
    protected $modules;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface[] $modules The modules.
     */
    public function __construct($modules)
    {
        $this->modules = $modules;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getServices()
    {
        $services = [];

        foreach ($this->modules as $module) {
            $services = array_merge($services, $module->getServices());
        }

        return $services;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
        // Run all modules
        foreach ($this->modules as $module) {
            $module->run($c);
        }
    }
}
