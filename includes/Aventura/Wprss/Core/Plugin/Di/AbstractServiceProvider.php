<?php

namespace Aventura\Wprss\Core\Plugin\Di;

use Aventura\Wprss\Core\Model\ModelAbstract;

/**
 * Common functionality for WPRA service providers.
 *
 * @since [*next-version*]
 */
abstract class AbstractServiceProvider extends ModelAbstract
{
    protected $serviceCache;

    /**
     * Retrieves the definitions that this service provides.
     *
     * Definitions are get exposed to the `services` event as 'definitions'.
     * The definition list is normalized to an array.
     * The eventual definitions list is cached.
     *
     * @since [*next-version*]
     *
     * @return array The definitions provided by this instance.
     */
    protected function _getServices()
    {
        if (is_null($this->serviceCache)) {
            $definitions = $this->_getServiceDefinitions();
            $this->event('services', array('definitions' => &$definitions));

            if (empty($definitions)) {
                $definitions = array();
            }

            if (!is_array($definitions)) {
                $definitions = (array) $definitions;
            }

            $this->serviceCache = $definitions;
        }

        return $this->serviceCache;
    }

    /**
     * The definitions provided by this instance.
     *
     * Not cached, not normalized. Override this in descendants class.
     *
     * @since [*next-version*]
     *
     * @return array The definition list.
     */
    protected function _getServiceDefinitions()
    {
        return array();
    }

    /**
     * Retrieves the prefix used by IDs of services that this instance provides.
     *
     * @since [*next-version*]
     *
     * @return string The prefix used for IDs of services.
     */
    protected function _getServiceIdPrefix()
    {
        return $this->_getDataOrConst('service_id_prefix', $this->_getEventPrefix());
    }
}
