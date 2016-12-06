<?php

if (!function_exists('wprss_autoloader')) {
    /**
     *
     * @return Aventura\Wprss\Core\Loader The loader singleton instance
     */
    function wprss_autoloader() {
        static $loader = null;
        $className = 'Aventura\\Wprss\\Core\\Loader';
        if (!class_exists($className)) {
            $dir = dirname(__FILE__);
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $className);
            $classPath = "{$dir}/{$classPath}.php";
            require_once($classPath);
        }

        if ($loader === null) {
            $loader = new $className();
            /* @var $loader Aventura\Wprss\Core\Loader */
            $loader->register();
        }

        return $loader;
    }
}

foreach(array(
    untrailingslashit(WPRSS_DIR) . '/vendor/autoload.php', // Standalone
    realpath(WPRSS_DIR . '/../../..') . '/vendor/autoload.php', // Vanilla WP, or another root package
) as $wprssAutoloadPath) {
    if (file_exists($wprssAutoloadPath)) {
        require_once($wprssAutoloadPath);
        break;
    }
}
