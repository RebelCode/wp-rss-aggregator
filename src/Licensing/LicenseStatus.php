<?php

namespace RebelCode\Wpra\Core\Licensing;

/**
 * An enum-style class for license statuses.
 *
 * @since [*next-version*]
 */
abstract class LicenseStatus
{
    /**
     * License status for when a license key is valid and active.
     *
     * @since [*next-version*]
     */
    const VALID = 'valid';

    /**
     * License status for when a license key is valid but inactive.
     *
     * @since [*next-version*]
     */
    const INACTIVE = 'inactive';

    /**
     * License status for when a license is invalid and (consequently) inactive.
     *
     * @since [*next-version*]
     */
    const INVALID = 'invalid';

    /**
     * License status for when a license key is valid but inactive for the current site.
     *
     * @since [*next-version*]
     */
    const SITE_INACTIVE = 'site_inactive';

    /**
     * License status for when a license key has expired and is thus invalid and inactive.
     *
     * @since [*next-version*]
     */
    const EXPIRED = 'expired';
}
