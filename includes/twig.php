<?php

/**
 * Retrieves the twig instance for WP RSS Aggregator.
 *
 * @since [*next-version*]
 *
 * @return Twig_Environment The twig instance.
 */
function wprss_twig()
{
    static $twig = null;

    if ($twig === null) {
        $options = [];

        if (defined('WP_DEBUG') && WP_DEBUG) {
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
 * @since [*next-version*]
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
 * @since [*next-version*]
 *
 * @param string $template The template name.
 * @param array  $context  The template context.
 *
 * @return string
 * @throws Twig_Error_Loader
 * @throws Twig_Error_Runtime
 * @throws Twig_Error_Syntax
 */
function wprss_render_template($template, $context = [])
{
    return wprss_twig()->load($template)->render($context);
}
