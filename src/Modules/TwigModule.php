<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Twig\WpraExtension;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\DebugExtension;
use Twig\Extensions\DateExtension;
use Twig\Extensions\I18nExtension;
use Twig\Extensions\TextExtension;
use Twig\Loader\FilesystemLoader;

/**
 * The Twig module for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class TwigModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            /*
             * The template loader for Twig.
             *
             * @since [*next-version*]
             */
            'wpra/twig' => function (ContainerInterface $c) {
                return new TwigEnvironment(
                    $c->get('wpra/twig/loader'),
                    $c->get('wpra/twig/options')
                );
            },
            /*
             * The template loader for Twig.
             *
             * @since [*next-version*]
             */
            'wpra/twig/loader' => function (ContainerInterface $c) {
                return new FilesystemLoader($c->get('wpra/twig/paths'));
            },
            /*
             * The template paths for Twig.
             *
             * @since [*next-version*]
             */
            'wpra/twig/paths' => function (ContainerInterface $c) {
                return [WPRSS_TEMPLATES];
            },
            /*
             * The twig debug option.
             *
             * @since [*next-version*]
             */
            'wpra/twig/debug' => function () {
                return defined('WPRSS_DEBUG') && WPRSS_DEBUG;
            },
            /*
             * The twig cache enabler option.
             *
             * @since [*next-version*]
             */
            'wpra/twig/cache_enabled' => function (ContainerInterface $c) {
                return !$c->get('wpra/twig/debug');
            },
            /*
             * The path to the Twig cache.
             *
             * @since [*next-version*]
             */
            'wpra/twig/cache' => function (ContainerInterface $c) {
                return get_temp_dir() . 'wprss/twig-cache';
            },
            /*
             * The options for Twig.
             *
             * @since [*next-version*]
             */
            'wpra/twig/options' => function (ContainerInterface $c) {
                return [
                    'debug' => $c->get('wpra/twig/debug'),
                    'cache' => $c->get('wpra/twig/cache_enabled') ? $c->get('wpra/twig/cache') : false,
                ];
            },
            /*
             * The extensions to use for WPRA's Twig instance.
             *
             * @since [*next-version*]
             */
            'wpra/twig/extensions' => function (ContainerInterface $c) {
                return [
                    $c->get('wpra/twig/extensions/i18n'),
                    $c->get('wpra/twig/extensions/date'),
                    $c->get('wpra/twig/extensions/text'),
                    $c->get('wpra/twig/extensions/debug'),
                    $c->get('wpra/twig/extensions/wpra'),
                ];
            },
            /*
             * The i18n extension for Twig.
             *
             * @since [*next-version*]
             */
            'wpra/twig/extensions/i18n' => function (ContainerInterface $c) {
                return new I18nExtension();
            },
            /*
             * The date extension for Twig.
             *
             * @since [*next-version*]
             */
            'wpra/twig/extensions/date' => function (ContainerInterface $c) {
                return new DateExtension();
            },
            /*
             * The text extension for Twig.
             *
             * @since [*next-version*]
             */
            'wpra/twig/extensions/text' => function (ContainerInterface $c) {
                return new TextExtension();
            },
            /*
             * The debug extension for Twig.
             *
             * @since [*next-version*]
             */
            'wpra/twig/extensions/debug' => function (ContainerInterface $c) {
                return new DebugExtension();
            },
            /*
             * The custom WPRA extension for Twig.
             *
             * @since [*next-version*]
             */
            'wpra/twig/extensions/wpra' => function (ContainerInterface $c) {
                return new WpraExtension();
            },
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return [
            /*
             * Registers the Twig extensions.
             *
             * @since [*next-version*]
             */
            'wpra/twig' => function (ContainerInterface $c, TwigEnvironment $twig) {
                foreach ($c->get('wpra/twig/extensions') as $extension) {
                    $twig->addExtension($extension);
                }

                return $twig;
            }
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
    }
}
