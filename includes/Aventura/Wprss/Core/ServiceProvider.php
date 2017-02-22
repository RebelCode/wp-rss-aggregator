<?php

namespace Aventura\Wprss\Core;

use Aventura\Wprss\Core\Plugin\Di\AbstractComponentServiceProvider;
use Aventura\Wprss\Core\Plugin\Di\ServiceProviderInterface;
use Interop\Container\ContainerInterface;
use Dhii\Di\FactoryInterface;
use Aventura\Wprss\Core\Plugin\ComponentInterface;
use Aventura\Wprss\Core\Model\Event\EventManagerInterface;

/**
 * Providers service definitions.
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
            $this->_p('plugin')                  => array($this, '_createPlugin'),
            $this->_p('factory')                 => array($this, '_createFactory'),
            $this->_p('event_manager')           => array($this, '_createEventManager'),
            $this->_p('logger')                  => array($this, '_createLogger'),
            $this->_p('admin_helper')            => array($this, '_createAdminHelper'),
            $this->_p('leave_review')            => array($this, '_createLeaveReview'),
            $this->_p('admin_ajax_notices')      => array($this, '_createAdminAjaxNotices'),
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
    public function getServiceIdPrefix($name = null)
    {
        return $this->_p($name);
    }

    /**
     * Creates the main plugin instance.
     *
     * @since [*next-version*]
     *
     * @return Plugin
     */
    public function _createPlugin(ContainerInterface $c, $p = null, $config = null)
    {
        $factory = $c->get($this->_p('factory'));
        $config = $this->_normalizeConfig($config, array(
            'basename'          => \WPRSS_FILE_CONSTANT,
            'name'              => \WPRSS_CORE_PLUGIN_NAME,
            'service_id_prefix' => \WPRSS_SERVICE_ID_PREFIX,
            'event_prefix'      => \WPRSS_EVENT_PREFIX,
        ), $config);
        $plugin = new Plugin($config, null, $c, $factory);

        $plugin->hook();

        return $plugin;
    }

    /**
     * Gets the reference to the factory.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c
     * @param null $p Previous definition.
     * @param array|null $config
     * @return FactoryInterface
     */
    public function _createFactory(ContainerInterface $c, $p = null, $config = null)
    {
        return wprss_core_container();
    }

    /**
     * Creates an event manager instance.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c
     * @param null $p
     * @param array $config
     * @return EventManagerInterface
     */
    public function _createEventManager(ContainerInterface $c, $p = null, $config = null)
    {
        $config = $this->_normalizeConfig($config, array(
            'is_keep_records'           => \WPRSS_DEBUG
        ));
        $service = new EventManager($config);

        return $service;
    }

    /**
     * Creates an instance of the admin helper component.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c
     * @param null $p
     * @param array $config
     * @return Component\AdminHelper
     */
    public function _createAdminHelper(ContainerInterface $c, $p = null, $config = null)
    {
        $config = $this->_normalizeConfig($config, array(
            'plugin'            => $c->get($this->_p('plugin'))
        ));
        $service = new Component\AdminHelper($config);
        $this->_prepareComponent($service);

        return $service;
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
        $service = new Component\AdminAjaxNotices($config);
        $this->_prepareComponent($service);

        return $service;
    }

    /**
     * Creates an instance of the leave-a-review component.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c
     * @param null $p
     * @param array $config
     * @return Component\LeaveReviewNotification
     */
    public function _createLeaveReview(ContainerInterface $c, $p = null, $config = null)
    {
        $config = $this->_normalizeConfig($config, array(
            'plugin'            => $c->get($this->_p('plugin'))
        ));
        $service = new Component\LeaveReviewNotification($config);
        $this->_prepareComponent($service);

        return $service;
    }

    /**
     * Creates an instance of the leave-a-review component.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c
     * @param null $p
     * @param array $config
     * @return Component\LeaveReviewNotification
     */
    public function _createLogger(ContainerInterface $c, $p = null, $config = null)
    {
        $config = $this->_normalizeConfig($config, array(
            'plugin'            => $c->get($this->_p('plugin')),
            'log_file_path'     => WPRSS_LOG_FILE . '-' . get_current_blog_id() . WPRSS_LOG_FILE_EXT,
            'level_threshold'   => wprss_log_get_level()
        ));
        $service = new Component\Logger($config);
        $this->_prepareComponent($service);

        return $service;
    }
}
