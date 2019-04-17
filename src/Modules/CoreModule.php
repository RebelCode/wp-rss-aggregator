<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Data\Wp\WpPluginInfoDataSet;

/**
 * The WP RSS Aggregator module that represents the core plugin.
 *
 * @since [*next-version*]
 */
class CoreModule implements ModuleInterface
{
    /**
     * The path to the plugin main file.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $pluginFilePath;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $pluginFilePath The path to the plugin main file.
     */
    public function __construct($pluginFilePath)
    {
        $this->pluginFilePath = $pluginFilePath;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            /*
             * The WP RSS Aggregator plugin info.
             *
             * @since [*next-version*]
             */
            'wpra/core/info' => function (ContainerInterface $c) {
                return new WpPluginInfoDataSet($c->get('wpra/core/plugin_file_path'));
            },
            /*
             * The WP RSS Aggregator core plugin version.
             *
             * @since [*next-version*]
             */
            'wpra/core/version' => function (ContainerInterface $c) {
                return $c->get('wpra/core/info')['version'];
            },
            /*
             * The path to the WP RSS Aggregator main plugin file.
             *
             * @since [*next-version*]
             */
            'wpra/core/plugin_file_path' => function () {
                return $this->pluginFilePath;
            },
            /*
             * The path to the WP RSS Aggregator plugin directory.
             *
             * @since [*next-version*]
             */
            'wpra/core/plugin_dir_path' => function (ContainerInterface $c) {
                return plugin_dir_path($c->get('wpra/core/plugin_file_path'));
            },
            /*
             * The URL to the WP RSS Aggregator plugin directory.
             *
             * @since [*next-version*]
             */
            'wpra/core/plugin_dir_url' => function (ContainerInterface $c) {
                return plugin_dir_url($c->get('wpra/core/plugin_file_path'));
            },
            /*
             * The path to the `includes` directory. Trailing slash is included.
             *
             * @since [*next-version*]
             */
            'wpra/core/includes_dir_path' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_path') . 'includes/';
            },
            /*
             * The path to the `templates` directory. Trailing slash is included.
             *
             * @since [*next-version*]
             */
            'wpra/core/templates_dir_path' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_path') . 'templates/';
            },
            /*
             * The URL to the directory where WP RSS Aggregator JS files can be found. Trailing slash is included.
             *
             * @since [*next-version*]
             */
            'wpra/core/js_dir_url' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_url') . 'js/';
            },
            /*
             * The URL to the directory where WP RSS Aggregator CSS files can be found. Trailing slash is included.
             *
             * @since [*next-version*]
             */
            'wpra/core/css_dir_url' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_url') . 'css/';
            },
            /*
             * The URL to the directory where WP RSS Aggregator images can be found. Trailing slash is included.
             *
             * @since [*next-version*]
             */
            'wpra/core/images_dir_url' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_url') . 'images/';
            },
            /*
             * The minimum WordPress version required by WP RSS Aggregator.
             *
             * @since [*next-version*]
             */
            'wpra/core/min_wp_version' => function () {
                return WPRSS_WP_MIN_VERSION;
            },
            /*
             * The minimum PHP version required by WP RSS Aggregator.
             *
             * @since [*next-version*]
             */
            'wpra/core/min_php_version' => function () {
                return WPRSS_MIN_PHP_VERSION;
            },
            /*
             * The current version of the WP RSS Aggregator database.
             *
             * @since [*next-version*]
             */
            'wpra/core/db_version' => function () {
                return WPRSS_DB_VERSION;
            },
            /*
             * The WP RSS Aggregator changelog.
             *
             * @since [*next-version*]
             */
            'wpra/core/changelog' => function (ContainerInterface $c) {
                $file = $c->get('wpra/core/changelog_file_path');
                $raw = file_get_contents($file);

                return $raw;
            },
            /*
             * The path to the WP RSS Aggregator changelog file.
             *
             * @since [*next-version*]
             */
            'wpra/core/changelog_file_path' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_path') . 'CHANGELOG.md';
            },
            /*
             * The WP RSS Aggregator plugin activation handler.
             *
             * @since [*next-version*]
             */
            'wpra/core/activation_handler' => function () {
                return 'wprss_activate';
            },
            /*
             * The WP RSS Aggregator plugin deactivation handler.
             *
             * @since [*next-version*]
             */
            'wpra/core/deactivation_handler' => function () {
                return 'wprss_deactivate';
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
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
        do_action('wpra/init');

        register_activation_hook($this->pluginFilePath , $c->get('wpra/core/activation_handler'));
        register_deactivation_hook($this->pluginFilePath , $c->get('wpra/core/deactivation_handler'));
    }
}
