<?php

namespace Aventura\Wprss\Core;

/**
 * A dummy plugin for the Core plugin.
 *
 * @since 4.8.1
 * @todo Create real Core plugin in the Core plugin.
 */
class Plugin extends Plugin\PluginAbstract
{
    const CODE = 'wprss';
    const VERSION = WPRSS_VERSION;
}