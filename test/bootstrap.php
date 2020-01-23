<?php

global $vendorDir;
$vendorDir = __DIR__ . '/../vendor/';

require_once $vendorDir . 'autoload.php';

/**
 * Loads a WordPress file.
 *
 * @since 4.17
 *
 * @param string $relPath The path relative to a WordPress installation's root directory.
 */
function wpraTestLoadWpFile($relPath)
{
    global $vendorDir;

    require_once $vendorDir . 'johnpbloch/wordpress-core/' . $relPath;
}

