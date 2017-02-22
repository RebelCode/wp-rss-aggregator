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
            $this->_pn('more_features')                 => array($this, '_createMoreFeaturesNotice'),
            $this->_pn('deleting_feed_items')           => array($this, '_createDeletingFeedItemsNotice')
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
     * Normalizes data of a notice.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $container DI container that will be used to retrieve notice controller.
     * @param array $data Data to normalize.
     * @return array Normalized data with defaults.
     */
    protected function _normalizeNoticeData(ContainerInterface $container, $data = array())
    {
        /* If using the notices controller to normalize on creation, the notice ID  will be
         * prefixed twice. This is because data is again auto normalized when adding the
         * notice to the controller. If ID is prefixed twice, this will cause nonce
         * validation to fail when trying to dismiss the notice.
         */
//        $noticeController = $container->get($this->_p('admin_ajax_notice_controller'));
        /* @var $noticeController \WPRSS_Admin_Notices */
//        $newData = $noticeController->normalize_notice_data($data);

//        return $newData;
        return $data;
    }

    /**
     * Creates a notice that informs users about other features.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c
     * @param null $p
     * @param array $config
     * @return Component\AdminAjaxNotices
     */
    public function _createMoreFeaturesNotice(ContainerInterface $c, $p = null, $config = null)
    {
        $helper = $c->get($this->_p('admin_helper'));
        $notice = $this->_createNotice(array(
            'id'                    => 'more_features',
            'notice_type'           => NoticeInterface::TYPE_UPDATED,
            'condition'             => $helper->createCommand(array($helper, 'isWprssPage')),
            'content'               => wpautop($this->__('Did you know that you can get more RSS features? Excerpts, thumbnails, keyword filtering, importing into posts and more... ') .
                                       $this->__(array('Check out the <a target="_blank" href="%1$s"><strong>extensions</strong></a> page.', 'http://www.wprssaggregator.com/extensions')))
        ), $c);

        return $notice;
    }

    /**
     * Creates a notice that informs users that feed items are deleting in the background.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c
     * @param null $p
     * @param array $config
     * @return Component\AdminAjaxNotices
     */
    public function _createDeletingFeedItemsNotice(ContainerInterface $c, $p = null, $config = null)
    {
        $helper = $c->get($this->_p('admin_helper'));
        $notice = $this->_createNotice(array(
            'id'                => 'deleting_feed_items',
            'condition'         => $helper->createCommand(array($helper, 'isWprssPage')),
            'content'           => wpautop($this->__('The feed items for this feed source are being deleted in the background.'))
        ), $c);

        return $notice;
    }

    /**
     * Crates a new admin notice instance.
     *
     * @since [*next-version*]
     *
     * @return NoticeInterface
     */
    protected function _createNotice($data, ContainerInterface $container)
    {
        $data = $this->_normalizeNoticeData($container, $data);
        $notice = new AdminAjaxNotice($data);

        return $notice;
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

    /**
     * Retrieve the prefix that is used by services that represent notices.
     *
     * @param string|null $id The ID to prefix, if not null.
     *
     * @since [*next-version*]
     */
    protected function _pn($id = null)
    {
        $prefix = $this->_getNoticeServiceIdPrefix();
        return static::stringHadPrefix($id)
            ? $id
            : "{$prefix}{$id}";
    }

    /**
     * Retrieves the prefix applied to IDs of services that represent notices.
     *
     * @since [*next-version*]
     *
     * @return string The prefix.
     */
    protected function _getNoticeServiceIdPrefix()
    {
        return $this->_getDataOrConst('notice_service_id_prefix');
    }
}