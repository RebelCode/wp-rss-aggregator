<?php

namespace Aventura\Wprss\Core;

use Aventura\Wprss\Core\Plugin\Di\AbstractContainer;
use Interop\Container\ServiceProvider as BaseServiceProvider;
use Interop\Container\ContainerInterface as BaseContainerInterface;
use Dhii\Di\FactoryInterface;
use Dhii\Di\WritableContainerInterface;

/**
 * The container that stores local, specific services.
 *
 * @since [*next-version*]
 */
class Container extends AbstractContainer implements FactoryInterface, WritableContainerInterface
{
    /**
     * @since [*next-version*]
     */
    public function __construct(BaseServiceProvider $serviceProvider, BaseContainerInterface $parent = null)
    {
        $this->_register($serviceProvider);
        if (!is_null($parent)) {
            $this->_setParentContainer($parent);
        }

        $this->_construct();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($id, array $config = array())
    {
        return $this->_make($id, $config);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function register(\Interop\Container\ServiceProvider $serviceProvieder)
    {
        $this->_register($serviceProvieder);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function set($id, $definition)
    {
        $this->_set($id, $definition);

        return $this;
    }
}
