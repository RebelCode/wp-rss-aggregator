<?php

global $vendorDir;
$vendorDir = __DIR__ . '/../vendor/';

require_once $vendorDir . 'autoload.php';

/**
 * Loads a WordPress file.
 *
 * @since [*next-version*]
 *
 * @param string $relPath The path relative to a WordPress installation's root directory.
 */
function wpraTestLoadWpFile($relPath)
{
    global $vendorDir;

    require_once $vendorDir . 'johnpbloch/wordpress-core/' . $relPath;
}

