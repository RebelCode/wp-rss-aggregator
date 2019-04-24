<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\Handlers\LoadTextDomainHandler;

/**
 * The WP RSS Aggregator internationalization module.
 *
 * @since 4.13
 */
class I18nModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function getFactories()
    {
        return [
            /*
             * The text domain for WP RSS Aggregator.
             *
             * @since 4.13
             */
            'wpra/i18n/domain' => function () {
                return WPRSS_TEXT_DOMAIN;
            },
            /*
             * The path to the languages directory.
             *
             * @since 4.13
             */
            'wpra/i18n/languages_dir_path' => function () {
                return WPRSS_LANG;
            },
            /*
             * The handler that loads the plugin's text domain.
             *
             * @since 4.13
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
     * @since 4.13
     */
    public function getExtensions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function run(ContainerInterface $c)
    {
        call_user_func($c->get('wpra/i18n/load_text_domain_handler'));
    }
}
