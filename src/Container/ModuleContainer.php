<?php

namespace RebelCode\Wpra\Core\Container;

use DI\NotFoundException;
use Interop\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\ModuleInterface;

/**
 * A container implementation specifically tailored for modules.
 *
 * @since [*next-version*]
 */
class ModuleContainer implements ContainerInterface
{
    /**
     * The inner container.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $inner;

    protected $definitions;

    protected $cache;

    protected $proxy;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface         $module The module instance.
     * @param ContainerInterface|null $proxy  Optional container to pass to service definitions.
     */
    public function __construct(ModuleInterface $module, ContainerInterface $proxy = null)
    {
        $this->definitions = $this->compileModuleServices($module);
        $this->useProxy($proxy);
        $this->cache = [];
    }

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface|null $proxy  Optional container to pass to service definitions.
     */
    public function useProxy(ContainerInterface $proxy = null)
    {
        $this->proxy = $proxy;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($id)
    {
        // If no definition for the given ID, throw an exception
        if (!$this->has($id)) {
            throw new NotFoundException(
                sprintf(__('Service "%s" was not found', 'wprss'), $id)
            );
        }

        // Invoke the definition and save the service in cache, if needed
        if (!array_key_exists($id, $this->cache)) {
            $container = ($this->proxy === null) ? $this : $this->proxy;
            $this->cache[$id] = call_user_func_array($this->definitions[$id], [$container]);
        }

        return $this->cache[$id];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function has($id)
    {
        return array_key_exists($id, $this->definitions);
    }

    /**
     * Compiles the module's service definitions.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface $module The module instance.
     *
     * @return callable[] The service definitions.
     */
    protected function compileModuleServices(ModuleInterface $module)
    {
        $factories = $module->getFactories();
        $extensions = $module->getExtensions();

        // Compile the factories and extensions into a flat definitions list
        $definitions = [];
        foreach ($factories as $key => $definition) {
            // Merge factory with its extension, if an extension exists
            if (array_key_exists($key, $extensions)) {
                $extension = $extensions[$key];
                $definition = function (ContainerInterface $c) use ($definition, $extension) {
                    return $extension($c, $definition($c));
                };
            }
            // Add to definitions
            $definitions[$key] = $definition;
        }

        return $definitions;
    }
}
