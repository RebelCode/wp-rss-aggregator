<?php

namespace RebelCode\Wpra\Core\Licensing;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use RebelCode\Wpra\Core\Wp\PluginInfo;

class Updater
{
    use NormalizeArrayCapableTrait;

    use CreateInvalidArgumentExceptionCapableTrait;

    use StringTranslatingTrait;

    protected $pluginSlug;

    protected $transient;

    protected $cacheEnabled;

    public function foo()
    {
        add_action('plugins_api', [$this, 'pluginsApi'], 15, 3);
    }

    public function pluginsApi($res, $action, $args)
    {
        if ($action !== 'plugin_information' || $args->slug !== 'my-plugin') {
            return false;
        }

        $info = $this->getPluginInfo($res, $action, $args);

        if (empty($info)) {
            return false;
        }

        return new PluginInfo();
    }

    protected function getPluginInfo($res, $action, $args)
    {
        $cache = $this->getCache();
        $info = ($this->cacheEnabled && $cache !== null)
            ? $cache
            : $this->fetchPluginInfo();

        if ($this->cacheEnabled) {
            $this->setCache($info);
        }

        return $info;
    }

    protected function getCache()
    {
        $cache = get_transient($this->transient);

        return ($cache === false)
            ? null
            : new PluginInfo($cache);
    }

    protected function setCache(PluginInfo $info)
    {
        set_transient($this->transient, $info->toArray(), DAY_IN_SECONDS);

        return $info;
    }

    /**
     * Fetches the plugin info from remote.
     *
     * @since 4.13
     *
     * @return object
     */
    abstract protected function fetchPluginInfo();
}
