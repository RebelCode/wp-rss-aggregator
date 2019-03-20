<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\Handlers\LoadTextDomainHandler;

/**
 * The WP RSS Aggregator internationalization module.
 *
 * @since [*next-version*]
 */
class I18nModule implements ModuleInterface
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
             * The text domain for WP RSS Aggregator.
             *
             * @since [*next-version*]
             */
            'wpra/i18n/domain' => function () {
                return WPRSS_TEXT_DOMAIN;
            },
            /*
             * The path to the languages directory.
             *
             * @since [*next-version*]
             */
            'wpra/i18n/languages_dir_path' => function () {
                return WPRSS_LANG;
            },
            /*
             * The handler that loads the plugin's text domain.
             *
             * @since [*next-version*]
             */
            'wpra/i18n/load_text_domain_handler' => function (ContainerInterface $c) {
                return new LoadTextDomainHandler(
                    $c->get('wpra/i18n/domain'),
                    $c->get('wpra/i18n/languages_dir_path')
                );
            }
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
        add_action('plugins_loaded', $c->get('wpra/i18n/load_text_domain_handler'));
    }
}
