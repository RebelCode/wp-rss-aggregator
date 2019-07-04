<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Wp\Asset\ScriptAsset;
use RebelCode\Wpra\Core\Wp\Asset\StyleAsset;

/**
 * The WP RSS Aggregator module that handles asset management and registration.
 *
 * @since [*next-version*]
 */
class AssetsModule implements ModuleInterface
{
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
        // Register assets for the admin side
        add_action('admin_enqueue_scripts', function () use ($c) {
            foreach ($c->get('wpra/assets/admin') as $asset) {
                $asset->register();
            }
        });

        // Register assets for the front side
        add_action('wp_enqueue_scripts', function () use ($c) {
            foreach ($c->get('wpra/assets/front') as $asset) {
                $asset->register();
            }
        });
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            'wpra/assets/admin' => function (ContainerInterface $c) {
                return [
                    $c->get('wpra/assets/scripts/manifest'),
                    $c->get('wpra/assets/scripts/vendor'),
                    $c->get('wpra/assets/styles/common'),
                ];
            },
            'wpra/assets/front' => function (ContainerInterface $c) {
                return [];
            },
            /*
             * Manifest file holds function used for bootstrapping and ordered loading of dependencies and application.
             *
             * @since [*next-version*]
             */
            'wpra/assets/scripts/manifest' => function() {
                return new ScriptAsset(
                    'wpra-manifest',
                    WPRSS_APP_JS . 'wpra-manifest.min.js',
                    [],
                    '0.1',
                    true
                );
            },
            /*
             * Vendor file holds all common dependencies for "compilable" applications.
             *
             * For example, `intro` pages application's and plugin's page application's files holds only logic for
             * that particular application. Common dependencies like Vue live in this file and loaded before that
             * application.
             *
             * @since [*next-version*]
             */
            'wpra/assets/scripts/vendor' => function() {
                return new ScriptAsset(
                    'wpra-vendor',
                    WPRSS_APP_JS . 'wpra-vendor.min.js',
                    ['wpra-manifest'],
                    '0.1',
                    true
                );
            },
            /*
             * The common styles.
             *
             * @since [*next-version*]
             */
            'wpra/assets/styles/common' => function () {
                return new StyleAsset('wpra-common', WPRSS_APP_CSS . 'common.min.css');
            },
        ];
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return [];
    }
}
