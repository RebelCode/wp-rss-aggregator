<?php

namespace Aventura\Wprss\Core\Model\AdminAjaxNotice;

use Aventura\Wprss\Core\Plugin\Di\AbstractComponentServiceProvider;
use Aventura\Wprss\Core\Plugin\Di\ServiceProviderInterface;
use Interop\Container\ContainerInterface;
use Aventura\Wprss\Core\Component\AdminAjaxNotices;

/**
 * Provides services that represent admin notices.
 *
 * @since [*next-version*]
 */
class ServiceProvider extends AbstractComponentServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getServiceDefinitions()
    {
        return array(
            $this->_p('admin_ajax_notice_controller')   => array($this, '_createAdminAjaxNoticeController'),
            $this->_p('admin_ajax_notices')             => array($this, '_createAdminAjaxNotices'),
        );
    }

    /**
     * Creates an instance of the admin AJAX notice controller.
     *
     * @uses-filter wprss_admin_notice_collection_before_init To modify collection before initialization.
     * @uses-filter wprss_admin_notice_collection_after_init To modify collection after initialization.
     * @uses-action wprss_admin_exclusive_scripts_styles To enqueue the scripts for the collection.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c
     * @param null $p Deprecated.
     * @param array $config
     * @return \WPRSS_Admin_Notices
     */
    public function _createAdminAjaxNoticeController(ContainerInterface $c, $p = null, $config = null)
    {
        $config = $this->_normalizeConfig($config, array(
            'setting_code'          => 'wprss_admin_notices',
            'id_prefix'             => 'wprss_',
            'text_domain'           => \WPRSS_TEXT_DOMAIN
        ));
        // Initialize collection
        $controller = new \WPRSS_Admin_Notices($config);
        $controller = apply_filters( \WPRSS_EVENT_PREFIX.'admin_notice_collection_before_init', $controller );
        $controller->init();
        $controller = apply_filters( \WPRSS_EVENT_PREFIX.'admin_notice_collection_after_init', $controller );

        return $controller;
    }

    /**
     * Creates an instance of the admin AJAX notices component.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c
     * @param null $p
     * @param array $config
     * @return Component\AdminAjaxNotices
     */
    public function _createAdminAjaxNotices(ContainerInterface $c, $p = null, $config = null)
    {
        $config = $this->_normalizeConfig($config, array(
            'plugin'            => $c->get($this->_p('plugin'))
        ));
        $service = new AdminAjaxNotices($config, $c);
        $this->_prepareComponent($service);

        return $service;
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
        return $this->_p($id);
    }
}