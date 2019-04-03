<?php

namespace RebelCode\Wpra\Core\Container;

use DI\ContainerBuilder;
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

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ModuleInterface $module The module instance.
     */
    public function __construct(ModuleInterface $module)
    {
        $this->inner = $this->createInnerContainer($this->compileModuleServices($module));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($id)
    {
        return $this->inner->get($id);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function has($id)
    {
        return $this->inner->has($id);
    }

    /**
     * Creates the inner container instance.
     *
     * @since [*next-version*]
     *
     * @param array $definitions The service definitions.
     *
     * @return ContainerInterface The created container instance.
     */
    protected function createInnerContainer(array $definitions)
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions($definitions);

        return $builder->build();
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
