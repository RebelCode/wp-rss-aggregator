<?php

namespace RebelCode\Wpra\Core\Licensing;

/**
 * Description
 *
 * @since 4.13
 */
interface PluginUpdaterInterface
{
    /**
     * Description
     *
     * @since 4.13
     */
    public function getChangelog();

    /**
     *
     *
     * @since 4.13
     *
     * @return mixed
     */
    public function getVersion();

    /**
     *
     *
     * @since 4.13
     *
     * @return mixed
     */
    public function getDownloadUrl();
}
