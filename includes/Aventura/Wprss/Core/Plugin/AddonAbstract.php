<?php

namespace Aventura\Wprss\Core\Plugin;

/**
 * A base class for SpinnerChief add-ons.
 *
 * @since [*next-version*]
 */
abstract class AddonAbstract extends PluginAbstract implements AddonInterface, ComponentInterface
{
    /** @since [*next-version*] */
    protected $_parent;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __construct($data, PluginInterface $parent, ComponentFactoryInterface $factory = null)
    {
        parent::__construct($data, $factory);
        $this->_setParent($parent);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getPlugin()
    {
        return $this->getParent();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getParent() {
        return $this->_parent;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _setParent(PluginInterface $parent) {
        $this->_parent = $parent;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getLogger()
    {
        if ($logger = parent::getLogger()) {
            return $logger;
        }

        return $this->getParent()->getLogger();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getEventManager()
    {
        if ($events = parent::getEventManager()) {
            return $events;
        }

        return $this->getParent()->getEventManager();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getEventPrefix($name = null)
    {
        $prefix = '';
        $prefix .= $this->getParent()->getEventPrefix();
        $prefix .= parent::getEventPrefix();
        if (is_null($name)) {
            return $prefix;
        }
        $prefix = static::stringHadPrefix($name)
            ? $name
            : "{$prefix}{$name}";

        return $prefix;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function on($name, $listener, $data = null, $priority = null, $acceptedArgs = null)
    {
        if (is_string($listener) && !is_object($listener)) {
            $listener = array($this, $listener);
        }

        return parent::on($name, $listener, $data, $priority, $acceptedArgs);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function event($name, $data = array())
    {
        if (!isset($data['caller'])) {
            $data['caller'] = $this;
        }

        return parent::event($name, $data);
    }
}