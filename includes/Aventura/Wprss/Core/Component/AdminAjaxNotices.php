<?php

namespace Aventura\Wprss\Core\Component;

use Aventura\Wprss\Core;
use Interop\Container\ContainerInterface;

/**
 * Component responsible for notices in the backend.
 *
 * @since 4.10
 */
class AdminAjaxNotices extends Core\Plugin\ComponentAbstract
{
    /**
     * @since [*next-version*]
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($data, ContainerInterface $container)
    {
        parent::__construct($data);

        $this->_setContainer($container);
    }

    /**
     * Sets the container that this component will use.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $container The container to set.
     * @return AdminAjaxNotices This instance.
     */
    protected function _setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Retrieves the container used by this instance.
     *
     * @since [*next-version*]
     *
     * @return ContainerInterface The container instance.
     */
    protected function _getContainer()
    {
        return $this->container;
    }

    /**
     * Retrieve the notice collection.
     *
     * @see wprss_admin_notice_get_collection()
     *
     * @since 4.10
     *
     * @return \WPRSS_Admin_Notices The notice collection object.
     */
    public function getNoticeCollection()
    {
        return wprss_admin_notice_get_collection();
    }

    /**
     * Add a notice.
     *
     * @see wprss_admin_notice_add()
     *
     * @param array $notice Data of the notice
     *
     * @return bool|WP_Error True if notice added, false if collection unavailable, or WP_Error if something went wrong.
     */
    public function addNotice($notice)
    {
        if (is_string($notice)) {
            $notice = $this->_getNotice($notice);
        }

        if ($notice instanceof Core\DataObjectInterface) {
            $notice = $notice->getData();
        }

        return wprss_admin_notice_add($notice);
    }

    /**
     * Retrieves a notice instance for the given ID.
     *
     * @since [*next-version*]
     *
     * @param string $name The unique notice name.
     * @return NoticeInterface The notice for the name.
     */
    public function getNotice($name)
    {
        return $this->_getNotice($name);
    }

    /**
     * Get a notice instance by its unique identifier.
     *
     * @since [*next-version*]
     *
     * @param string $name Unique name of the notice.
     * @return NoticeInterface The notice.
     */
    protected function _getNotice($name)
    {
        $serviceId = $this->_p($name);

        return $this->_getContainer()->get($serviceId);
    }

    /**
     * Determine whether or not a notice with the specified name exists.
     *
     * @since [*next-version*]
     *
     * @param string $name The unique name of the notice.
     * @return bool True if a notice with the specified name exists; false otherwise.
     */
    protected function _hasNotice($name)
    {
        $id = $this->_p($name);

        return $this->_getContainer()->has($id);
    }

    /**
     * Gets the service ID for a notice name.
     *
     * @since [*next-version*]
     *
     * @param string $noticeName The unique notice name.
     * @return string The service ID that corresponds to the given name.
     */
    protected function _p($noticeName)
    {
        return static::stringHadPrefix($noticeName)
            ? $noticeName
            : WPRSS_NOTICE_SERVICE_ID_PREFIX . $noticeName;
    }
}
