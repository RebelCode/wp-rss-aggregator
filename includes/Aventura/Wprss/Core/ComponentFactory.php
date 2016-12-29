<?php

// This whole namespace is a temporary one, until there's a real Core add-on
namespace Aventura\Wprss\Core;

/**
 * A dummy factory of Core components.
 *
 * This is to be used with the Core plugin.
 *
 * @todo Create a real Core factory of Core components in the Core plugin.
 * @since 4.8.1
 */
class ComponentFactory extends Plugin\ComponentFactoryAbstract
{
    /**
     * @since 4.8.1
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setBaseNamespace(__NAMESPACE__ . '\\Component');
    }

    public function createLogger($data = array())
    {
        $logger = $this->createComponent('Logger', $this->getPlugin());
        if (!isset($data['log_file_path'])) {
            $data['log_file_path'] = WPRSS_LOG_FILE . '-' . get_current_blog_id() . WPRSS_LOG_FILE_EXT;
        }
        if (!isset($data['level_threshold'])) {
            $data['level_threshold'] = wprss_log_get_level();
        }
        $logger->addData($data);

        return $logger;
    }

    /**
     * @since 4.8.1
     * @param array $data
     * @return Model\Event\EventManagerInterface
     */
    public function createEventManager($data = array())
    {
        $events = $this->createComponent('EventManager', $this->getPlugin(), $data);
        return $events;
    }

    /**
     * Creates a component that is responsible for the "Leave a Review" notification.
     *
     * @since 4.10
     *
     * @param array $data Additional data to use for component configuration.
     * @return Component\LeaveReviewNotification
     */
    public function createLeaveReviewNotification($data = array())
    {
        $component = $this->createComponent('LeaveReviewNotification', $this->getPlugin(), $data);

        return $component;
    }

    /**
     * Creates a component that is responsible for the admin notices.
     *
     * @since 4.10
     *
     * @return Component\AdminAjaxNotices
     */
    public function createAdminAjaxNotices($data = array())
    {
        $component = $this->createComponent('AdminAjaxNotices', $this->getPlugin(), $data);

        return $component;
    }

    /**
     * Creates a helper component related to the backend.
     *
     * @since 4.10
     *
     * @return Component\AdminHelper
     */
    public function createAdminHelper($data = array())
    {
        $component = $this->createComponent('AdminHelper', $this->getPlugin(), $data);

        return $component;
    }
}