<?php

namespace RebelCode\Wpra\Core\Wp\Asset;

/**
 * Class for enqueuing scripts that were built using the Webpack.
 *
 * @since [*next-version*]
 */
class ApplicationScriptAsset extends ScriptAsset
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function enqueue()
    {
        wprss_plugin_enqueue_app_scripts($this->handle, $this->src, $this->dependencies, $this->version, $this->inFooter);
    }
}
