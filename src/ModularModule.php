<?php

namespace RebelCode\Wpra\Core;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\ModuleInterface;

/**
 * An implementation of a module that is modular - i.e. made up of modules.
 *
 * @since [*next-version*]
 */
class ModularModule implements ModuleInterface
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
    public function getFactories()
    {
        $services = [];

        foreach ($this->modules as $module) {
            $services = array_merge($services, $module->getFactories());
        }

        return $services;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        $extensions = [];

        foreach ($this->modules as $module) {
            foreach ($module->getExtensions() as $key => $extension) {
                if (array_key_exists($key, $extensions)) {
                    $current = $extensions[$key];
                    $extension = function (ContainerInterface $c, $previous) use ($current, $extension) {
                        $result1 = $current($c, $previous);
                        $result2 = $extension($c, $result1);

                        return $result2;
                    };
                }

                $extensions[$key] = $extension;
            }
        }

        return $extensions;
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
