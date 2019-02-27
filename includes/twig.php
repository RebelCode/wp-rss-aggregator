<?php

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

        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            $options['cache'] = get_temp_dir() . 'wprss/twig-cache';
        }

        $loader = new Twig_Loader_Filesystem(WPRSS_TEMPLATES);
        $twig = new Twig_Environment($loader, $options);
    }

    return $twig;
}

/**
 * Loads a WPRSS twig template.
 *
 * @since 4.12
 *
 * @param string $template The tmeplate name.
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
