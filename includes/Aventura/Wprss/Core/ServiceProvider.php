<?php

namespace Aventura\Wprss\Core;

use Aventura\Wprss\Core\Plugin\Di\AbstractServiceProvider;
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
class ServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    const PREFIX_OVERRIDE = '!';
    const COMPONENT_INTERFACE = 'Aventura\\Wprss\\Core\\Plugin\\ComponentInterface';

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
        $prefix = $this->_getServiceIdPrefix();
        return static::stringHadPrefix($name)
            ? $name
            : "{$prefix}{$name}";
    }

    /**
     * Alias of `getServiceIdPrefix()`.
     *
     * @see getServiceIdPrefix().
     *
     * @since [*next-version*]
     */
    protected function _p($name = null)
    {
        return $this->getServiceIdPrefix($name);
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
     * Throws an exception if given instance or class name is not a valid component or component class name.
     *
     * @since [*next-version*]
     *
     * @param string|ComponentInterface|mixed $component
     * @throws Exception If the argument is not a valid component instance or class name.
     */
    protected function _assertComponent($component)
    {
        if (!is_a($component, static::COMPONENT_INTERFACE)) {
            $componentType = is_string($component)
                    ? $component
                    : (is_object($component)
                            ? get_class($component)
                            : get_type($component));
            throw $this->exception(array('"%1$s" is not a component', $componentType));
        }
    }

    /**
     * Prepares a component instance.
     *
     * @since [*next-version*]
     *
     * @param ComponentInterface $component The component to prepare.
     * @return ComponentInterface The prepared component.
     */
    protected function _prepareComponent($component)
    {
        $this->_assertComponent($component);
        $component->hook();

        return $component;
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

    /**
     * Normalizes a factory config, optionally by using defaults.
     *
     * @since [*next-version*]
     *
     * @param array|null $config The config to normalize.
     * @param array $defaults Defaults, if any, which will be extended by the normalized config.
     * @return array The normalized config, optionally applied on top of defaults.
     */
    protected function _normalizeConfig($config, $defaults = array())
    {
        if (is_null($config)) {
            $config = array();
        }

        return $this->_arrayMergeRecursive($defaults, $config);
    }

    /**
     * Merges two arrays recursively, preserving element types.
     *
     * @since [*next-version*]
     *
     * @see \array_merge_recursive_distinct()
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function _arrayMergeRecursive(&$array1, &$array2)
    {
        return \array_merge_recursive_distinct($array1, $array2);
    }
}
