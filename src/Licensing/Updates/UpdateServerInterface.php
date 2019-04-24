<?php

namespace RebelCode\Wpra\Core\Licensing\Updates;

use RebelCode\Wpra\Core\Wp\PluginInfo;

interface UpdateServerInterface
{
    /**
     *
     *
     * @since 4.13
     *
     * @return PluginInfo
     */
    public function getPluginInfo();
}
