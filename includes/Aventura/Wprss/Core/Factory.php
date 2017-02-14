<?php

namespace Aventura\Wprss\Core;

use Dhii\Di\FactoryInterface;

/**
 * @deprecated [*next-version*] Here only for BC.
 * @since 4.8.1
 */
class Factory extends Plugin\FactoryAbstract
{
    /**
     * Creates the plugin instance.
     *
     * @since 4.8.1
     * @param array $data Data for the plugin.
     * @return \Aventura\Wprss\Core\Plugin
     */
    public function _create($data = array())
    {
        $plugin = $this->_getFactory()->make($this->_p('plugin'), $data);

        return $plugin;
    }

    /**
     * Prefixes a service name with the WPRA service ID prefix.
     *
     * @since [*next-version*]
     *
     * @param string $name A service name.
     * @return string The prefixed name.
     */
    protected function _p($name)
    {
        return \WPRSS_SERVICE_ID_PREFIX . $name;
    }

    /**
     * Gets service the factory.
     *
     * @since [*next-version*]
     *
     * @return FactoryInterface The factory.
     */
    protected function _getFactory()
    {
        return $this->_getContainer()->get($this->_p('factory'));
    }

    /**
     * Retrieve the DI container.
     *
     * @since [*next-version*]
     * @return ContainerInterface The container instance.
     */
    protected function _getContainer()
    {
        return wprss_wp_container();
    }
}