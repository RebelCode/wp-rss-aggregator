<?php

namespace Aventura\Wprss\Core\Model\AdminAjaxNotice;

use Aventura\Wprss\Core\Plugin\Di\AbstractServiceProvider;
use Aventura\Wprss\Core\Plugin\Di\ServiceProviderInterface;

/**
 * Provides services that represent admin notices.
 *
 * @since [*next-version*]
 */
class ServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getServiceDefinitions()
    {
        return array(
            // Add notice services here
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getServices()
    {
        return $this->_getServices();
    }
    
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getServiceIdPrefix($id = null)
    {
        return $this->_p($name);
    }
}