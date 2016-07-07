<?php

namespace Aventura\Wprss\Core\Plugin;

use Aventura\Wprss\Core;

/**
 * The base class for all WP plugins.
 *
 * @since 4.8.1
 */
class PluginAbstract extends Core\Model\ModelAbstract implements PluginInterface
{
    const CODE = '';
    const VERSION = '';

    /** @since 4.8.1 */
    protected $_factory;
    /** @since 4.8.1 */
    protected $_logger;
    /** @since 4.8.1 */
    protected $_eventManager;

    /**
     *
     * @param array|string $data Data that describes the plugin.
     *  The following indices are required:
     *      * `basename`            - The plugin basename, or full path to plugin's main file. See {@see getBasename()}.
     *  Other indices explicitly handled by this class:
     *      * `component_factory`   - Instance or name of a component factory class.
     *      * `text_domain`         - The text domain used for translation by this plugin. See {@see getTextDomain}.
     *      * `name`                - The human-readable name of the plugin. See {@see getName()}.
     * Any other data will just be added to this instances internal data.
     * @param ComponentFactoryInterface A factory that will create components for this plugin.
     *
     * @throws Exception If required fields are not specified.
     */
    public function __construct($data, ComponentFactoryInterface $factory = null)
    {
        if (!is_array($data)) {
            $data = array('basename' => $data);
        }

        // Handling basename
        if (!isset($data['basename'])) {
            throw $this->exception('Could not create plugin instance: "basename" must be specified', array(__NAMESPACE__, 'Exception'));
        }
        $data['basename'] = static::standardizeBasename($data['basename']);

        // Normalizing and setting component factory
        if (is_null($factory) && isset($data['component_factory'])) {
            $factory = $data['component_factory'];
        }

        if ($factory) {
            $this->setFactory($factory);
        }

        parent::__construct($data);
    }

    public function getBasename()
    {
        return $this->getData('basename');
    }

    public function getTextDomain()
    {
        return $this->getData('text_domain');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function getCode()
    {
        return $this->_getDataOrConst('code');
    }

    public function getVersion()
    {
        return $this->_getDataOrConst('version');
    }

    /**
     * @since 4.8.1
     * @return ComponentFactoryInterface
     */
    public function getFactory()
    {
        return $this->_factory;
    }

    public function setFactory(ComponentFactoryInterface $factory)
    {
        $this->_setFactory($factory);
        return $this;
    }

    public function isActive()
    {
        return static::isPluginActive($this);
    }

    public function deactivate()
    {
        static::deactivatePlugin($this);
        return $this;
    }

    /**
     * Checks if a plugin is active.
     *
     * @since 4.8.1
     * @param PluginInterface|string $plugin A plugin instance or basename.
     * @return bool True if the plugin is active; false otherwise.
     */
    static public function isPluginActive($plugin)
    {
        static::_ensurePluginFunctionsExist();

        if ($plugin instanceof PluginInterface) {
            $plugin = $plugin->getBasename();
        }

        return is_plugin_active($plugin);
    }

    static public function deactivatePlugin($plugin)
    {
        static::_ensurePluginFunctionsExist();

        if ($plugin instanceof PluginInterface) {
            $plugin = $plugin->getBasename();
        }

        deactivate_plugins($plugin);
    }

    static protected function _ensurePluginFunctionsExist()
    {
        // Making sure there are the functions we need
		if (!function_exists( 'is_plugin_active' )) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
    }

    /**
     * Sets the component factory instance.
     *
     * If class name given instead, it will be instantiated.
     *
     * @since 4.8.1
     * @param ComponentFactoryInterface|string $factory The component factory instance or class name.
     * @return PluginInterface This instance.
     * @throws Exception If factory class specified as classname string does not exist, or is not a factory.
     */
    protected function _setFactory(ComponentFactoryInterface $factory) {
        // Factory could be a classname
        if (is_string($factory)) {
            // Making sure it exists
            $factory = trim($factory);
            if (!class_exists($factory)) {
                throw $this->exception(array('Could not set component factory: Factory class "%1$s" does not exist', $factory), array(__NAMESPACE__, 'Exception'));
            }
            // Making sure it's a factory
            if (!is_a($factory, __NAMESPACE__ . '\ComponentFactoryInterface')) {
                throw $this->exception(array('Could not set component factory: Factory class "%1$s" is not a factory', $factory), array(__NAMESPACE__, 'Exception'));
            }

            $factory = new $factory();
            /* @var $factory Aventura\Wprss\Core\Plugin\ComponentFactoryInterface */
        }

        $this->_factory = $factory;
        return $this;
    }

    /**
     * Translates some text.
     *
     * @since 4.8.1
     * @param string $text The text to translate.
     * @param string|null The text domain to use for translation.
     *  Defaults to this plugin's text domain.
     * @return string Translated text
     */
    protected function _translate($text, $translator = null)
    {
        if (!is_null($translator)) {
            $translator = $this->getTextDomain();
        }

        return __($text, $translator);
    }

    /**
     * Gets a plugin basename from its absolute path.
     *
     * @since 4.8.1
     * @param string $path Absolute path to a plugin's main file.
     * @return string The path to the plugin's main file, relative to the plugins directory.
     */
    public static function getPluginBasename($path) {
        return plugin_basename($path);
    }

    /**
     * Gets the logger instance used by this plugin.
     *
     * @since 4.8.1
     * @return Core\Model\LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Sets the logger instance to be used by this plugin.
     *
     * @since 4.8.1
     * @param Core\Model\LoggerInterface $logger
     * @return Core\Plugin\PluginAbstract
     */
    public function setLogger(Core\Model\LoggerInterface $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    public function log($level, $message, array $context = array())
    {
        $isFormattable = is_array($message) && isset($message[0]) && is_string($message[0]);
        if (is_object($message) || empty($message) || (!is_string($message) && !$isFormattable)) {
            return $this->logObject($level, $message, $context);
        }

        if ($logger = $this->getLogger()) {
            try {
                $message = $this->__($message);
            } catch (\InvalidArgumentException $e) {
                return $this->logObject($level, $message, $context);
            }
            return $logger->log($level, $message, $context);
        }

        return false;
    }

    public function logObject($level, $object, array $context = array())
    {
        if (empty($object)) {
            ob_start();
            var_dump($object);
            $dump = ob_get_contents();
            ob_end_clean();
        }
        else {
            $dump = print_r($object, true);
        }

        return $this->log($level, $dump, $context);
    }

    /**
     * A default no-op implementation. Does nothing. Override in descendants.
     *
     * @since 4.8.1
     */
    public function hook() {}

    /**
     * {@inheritdoc}
     *
     * @since 4.8.1
     */
    protected function _getEventPrefix($name = null)
    {
        $prefix = $this->hasData('event_prefix')
            ? $this->getData('event_prefix')
            : ($code = $this->getCode()) ? sprintf('%1$s_', $code) : '';

        return string_had_prefix($name, $this->getPrefixOverride())
            ? $name
            : "{$prefix}{$name}";
    }

    /**
     * Sets the event manager for this instance.
     *
     * @since 4.8.1
     * @param Core\Model\Event\EventManagerInterface $manager An event manager.
     * @return PluginAbstract This instance.
     */
    public function setEventManager(Core\Model\Event\EventManagerInterface $manager)
    {
        $this->_eventManager = $manager;
        return $this;
    }

    /**
     * Retrieves this instance's event manager.
     *
     * @since 4.8.1
     * @return Core\Model\Event\EventManagerInterface|null The event manager of this instance, or null if not set.
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.8.1
     */
    public function on($name, $listener, $data = null, $priority = null, $acceptedArgs = null)
    {
        if (is_string($listener) && !is_object($listener)) {
            $listener = array($this, $listener);
        }

        if ($events = $this->getEventManager()) {
            $name = $this->getEventPrefix($name);
            return $events->on($name, $listener, $data, $priority, $acceptedArgs);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.8.1
     */
    public function event($name, $data = array())
    {
        if (!isset($data['caller'])) {
            $data['caller'] = $this;
        }

        if ($events = $this->getEventManager()) {
            $name = $this->getEventPrefix($name);
            return $events->event($name, $data);
        }

        return null;
    }

    /**
     * Converts all directory separators into Unix-style ones.
     *
     * @since 4.9
     * @param string $path A filesystem path.
     * @return The path with standardized directory separators, and trimmed
     *  whitespace.
     */
    public static function standardizeDirectorySeparators($path)
    {
        return trim(str_replace(array('\\', '/'), '/', $path));
    }

    /**
     * Will standardize a plugin basename.
     *
     * A standard plugin basename is a path to the main plugin file relative
     * to the plugins directory, and with Unix directory separators if
     * applicable.
     *
     * @since 4.9
     * @see standardizeDirectorySeparators()
     * @param string $path An absolute or relative path to a plugin main file.
     * @return string A standardized plugin basename.
     */
    public static function standardizeBasename($path)
    {
        $path = static::standardizeDirectorySeparators($path);

        // Account for full path to main file.
        if (substr($path, 0, 1) === '/' || substr_count($path, '/') >= 2) {
            $path = static::getPluginBasename($path);
        }

        return $path;
    }
}
