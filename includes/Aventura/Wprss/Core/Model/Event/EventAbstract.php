<?php

namespace Aventura\Wprss\Core\Model\Event;

use Aventura\Wprss\Core;

/**
 * @since [*next-version*]
 */
abstract class EventAbstract extends Core\DataObject implements EventInterface
{
    /** @since [*next-version*] */
    protected $_name;

    /**
     * @since [*next-version*]
     * @param array|string $data The event's data. Must have a 'name' index.
     *  If string, will be used as 'name'.
     */
    public function __construct($data)
    {
        if (!is_array($data)) {
            $data = array('name' => $data);
        }
        
        parent::__construct($data);
    }

    /**
     * @since [*next-version*]
     * @throws Core\Exception If 'name' index is not present.
     */
    protected function _construct()
    {
        parent::_construct();

        if (!$this->hasData('name')) {
            throw new Exception('Could not create event: Name must be specified');
        }

        $this->_setName($this->getData('name'));
    }

    /**
     * Get the name of this event
     *
     * @since [*next-version*]
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Blocks setting name from outside.
     * 
     * If used, a notice will be emitted.
     *
     * @since [*next-version*]
     * @access protected
     * @param string $name
     * @return EventAbstract This instance.
     */
    public function setName($name)
    {
        trigger_error('Event name can only be set upon creation', E_USER_NOTICE);
        return $this;
    }

    /**
     * Set this event's name.
     *
     * @since [*next-version*]
     * @param string $name
     * @return EventAbstract This instance.
     */
    protected function _setName($name)
    {
        $this->_name = trim($name);
        return $this;
    }
}