<?php

namespace Aventura\Wprss\Core;

use Aventura\Wprss\Core\Plugin\Di\AbstractContainer;
use Interop\Container\ServiceProvider as BaseServiceProvider;
use Interop\Container\ContainerInterface as BaseContainerInterface;
use Dhii\Di\FactoryInterface;

/**
 * The container that stores local, specific services.
 *
 * @since [*next-version*]
 */
class Container extends AbstractContainer implements FactoryInterface
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
}
