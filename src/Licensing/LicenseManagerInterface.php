<?php

namespace RebelCode\Wpra\Core\Licensing;

use Dhii\Validation\Exception\ValidationExceptionInterface;
use RuntimeException;

/**
 * Interface for objects that can activate and deactivate licenses.
 *
 * @since [*next-version*]
 */
interface LicenseManagerInterface
{
    /**
     * Activates the given license.
     *
     * @since [*next-version*]
     *
     * @param LicenseInterface $license The license to activate.
     *
     * @throws ValidationExceptionInterface If the license is invalid.
     * @throws RuntimeException If an error occurred and the license failed to be activated.
     */
    public function activate(LicenseInterface $license);

    /**
     * Deactivates the given license.
     *
     * @since [*next-version*]
     *
     * @param LicenseInterface $license The license to deactivate.
     *
     * @throws ValidationExceptionInterface If the license is invalid.
     * @throws RuntimeException If an error occurred and the license failed to be deactivated.
     */
    public function deactivate(LicenseInterface $license);

    /**
     * Retrieves the information about a given license.
     *
     * @since [*next-version*]
     *
     * @param LicenseInterface $license The license.
     *
     * @throws RuntimeException If an error occurred while retrieving the license info.
     */
    public function getInfo(LicenseInterface $license);
}
