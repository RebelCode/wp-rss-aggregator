<?php

namespace Aventura\Wprss\Core\Util;

/**
 * Common functionality for retrieving all of the plugin's general settings, merge it with defaults and cache it.
 *
 * @since [*next-version*]
 */
trait GetGeneralSettingsTrait
{
    /**
     * A cache of the general settings.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $generalSettingsCache;

    /**
     * Retrieves the general settings, merged with their defaults, and caches it.
     *
     * @since [*next-version*]
     *
     * @return array The settings array.
     */
    protected function _getGeneralSettings()
    {
        if (empty($this->generalSettingsCache)) {
            $this->generalSettingsCache = get_option('wprss_settings_general');
            $this->generalSettingsCache = wp_parse_args($this->generalSettingsCache, wprss_get_default_settings_general());
        }

        return $this->generalSettingsCache;
    }
}
