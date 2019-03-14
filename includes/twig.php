<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TemplateWrapper;

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
 * @return Environment The twig instance.
 */
function wprss_twig()
{
    return wpra_container()->get('wpra/twig');
}

/**
 * Loads a WPRSS twig template.
 *
 * @since 4.12
 *
 * @param string $template The template name.
 *
 * @return TemplateWrapper
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
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
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
function wprss_render_template($template, $context = [])
{
    return wprss_twig()->load($template)->render($context);
}

/**
 * Retrieves custom WP RSS Aggregator Twig filters.
 *
 * @since [*next-version*]
 *
 * @return array
 */
function wprss_get_twig_custom_filters()
{
    return [
        'wpralink' => [
            'function' => function ($text, $url, $flag) {
                return wprss_link_display($url, $text, $flag);
            },
            'options' => [
                'is_safe' => ['html'],
            ],
        ],
    ];
}
