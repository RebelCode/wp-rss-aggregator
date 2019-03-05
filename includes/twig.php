<?php

use Twig\Environment;
use Twig\Extensions\DateExtension;
use Twig\Extensions\I18nExtension;
use Twig\Extensions\TextExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

if (defined('WPRSS_TWIG_MIN_PHP_VERSION')) {
    return;
}

// Minimum version requirement for twig
define('WPRSS_TWIG_MIN_PHP_VERSION', '5.4.0');

/**
 * Returns whether twig can be used.
 *
 * @since 4.12.1
 *
 * @return bool True if twig can be used, false if not.
 */
function wprss_can_use_twig()
{
    return version_compare(PHP_VERSION, WPRSS_TWIG_MIN_PHP_VERSION, '>=');
}

/**
 * Retrieves the twig instance for WP RSS Aggregator.
 *
 * @since 4.12
 *
 * @return Twig_Environment The twig instance.
 */
function wprss_twig()
{
    static $twig = null;

    if ($twig === null) {
        $options = array();

        // If WP_DEBUG is turned off, use Twig's compiled template cache
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            $options['cache'] = get_temp_dir() . 'wprss/twig-cache';
        }

        // Retrieve the template paths
        $paths = [WPRSS_TEMPLATES];
        $paths = apply_filters('wprss_template_paths', $paths);

        // Set up the twig loader and the environment instances
        $loader = new FilesystemLoader($paths);
        $twig = new Environment($loader, $options);

        $twig->addFunction(
            new Twig_SimpleFunction('wpra_link', function ($text, $url, $b = true) {
                return wprss_link_display($url, $text, $b);
            })
        );
    }

    return $twig;
}

/**
 * Loads a WPRSS twig template.
 *
 * @since 4.12
 *
 * @param string $template The template name.
 *
 * @return Twig_TemplateWrapper
 * @throws Twig_Error_Loader
 * @throws Twig_Error_Runtime
 * @throws Twig_Error_Syntax
 */
function wprss_load_template($template)
{
    return wprss_twig()->load($template);
}

/**
 * Loads and renders a WPRSS template.
 *
 * @since 4.12
 *
 * @param string $template The template name.
 * @param array  $context  The template context.
 *
 * @return string
 * @throws Twig_Error_Loader
 * @throws Twig_Error_Runtime
 * @throws Twig_Error_Syntax
 */
function wprss_render_template($template, $context = array())
{
    return wprss_twig()->load($template)->render($context);
}
