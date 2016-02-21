<?php

namespace Aventura\Wprss\Core\Plugin;

/**
 * Common functionality of component factories.
 * A component factory is also a component ;P
 *
 * @since 4.8.1
 */
abstract class ComponentFactoryAbstract extends ComponentAbstract implements ComponentFactoryInterface
{
    /**
     * Creates a new component instance.
     *
     * @since 4.8.1
     * @param string $class The classname of the component to create.
     *  Can be relative to the base namespace of this factory.
     * @param PluginInterface $parent The parent plugin for the new component.
     * @return ComponentInterface A new component.
     * @throws Exception If class does not exist, or is not a component class.
     */
    public function createComponent($class, PluginInterface $parent, array $data = array())
    {
        $className = $this->getComponentClassName($class);
        $componentBase = 'Aventura\Wprss\Core\Plugin\ComponentInterface';
        if (!static::classImplements($className, $componentBase)) {
            throw $this->exception(array('Could not create component: "%1$s" is not a component class as it does not implement "%2$s"', $className, $componentBase), array(__NAMESPACE__, 'Exception'));
        }

        if (!class_exists($className)) {
            throw $this->exception(array('Could not create component: component class"%1$s" does not exist', $className), array(__NAMESPACE__, 'Exception'));
        }

        $data['plugin'] = $parent;
        $component = new $className($data);
        $component->hook();
        
        return $component;
    }

    /**
     * Get the name of a component class, based on it's relative or absolute name, or mapped ID.
     *
     * @since 4.8.1
     * @param string $className A relative or absolute class name, or some other class identifier that is mapped
     *  to a class name. If relative, then relative to the {@see getBaseNamespace()}.
     * @return string Name of the component class.
     */
    public function getComponentClassName($className)
    {
        // Namespace specified as array of parts; assume root namespace
        if (is_array($className)) {
            $className = '\\' . trim(implode('\\', $className), '\\');
        }
        
        if (static::isRootNamespace($className)) {
            return $className;
        }

        $rootNamespace = $this->getBaseNamespace();
        return sprintf('%1$s\\%2$s', $rootNamespace, $className);
    }
}