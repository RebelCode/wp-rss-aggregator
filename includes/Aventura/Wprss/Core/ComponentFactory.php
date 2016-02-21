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
}