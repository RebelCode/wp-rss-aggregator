<?php

namespace RebelCode\Wpra\Core\Licensing;

/**
 * Interface for objects that represent a license.
 *
 * @since 4.13
 */
interface LicenseInterface
{
    /**
     * Retrieves the license's key.
     *
     * @since 4.13
     *
     * @return string
     */
    public function getKey();

    /**
     * Retrieves the license's status.
     *
     * @since 4.13
     *
     * @return string
     */
    public function getStatus();

    /**
     * Retrieves the license's expiry timestamp.
     *
     * @since 4.13
     *
     * @return int
     */
    public function getExpiry();

    /**
     * Retrieves the information about the license holder.
     *
     * @since 4.13
     *
     * @return string
     */
    public function getHolder();

    /**
     * Retrieves the number of activations for this license.
     *
     * @since 4.13
     *
     * @return int
     */
    public function getActivationCount();

    /**
     * Retrieves the activation limit for this license.
     *
     * @since 4.13
     *
     * @return int
     */
    public function getActivationLimit();
}
