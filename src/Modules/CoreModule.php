<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Data\ChangelogDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpPluginInfoDataSet;

/**
 * The WP RSS Aggregator module that represents the core plugin.
 *
 * @since 4.13
 */
class CoreModule implements ModuleInterface
{
    /**
     * The path to the plugin main file.
     *
     * @since 4.13
     *
     * @var string
     */
    protected $pluginFilePath;

    /**
     * Constructor.
     *
     * @since 4.13
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
     * @since 4.13
     */
    public function getFactories()
    {
        return [
            /*
             * The WP RSS Aggregator plugin info.
             *
             * @since 4.13
             */
            'wpra/core/info' => function (ContainerInterface $c) {
                return new WpPluginInfoDataSet($c->get('wpra/core/plugin_file_path'));
            },
            /*
             * The WP RSS Aggregator core plugin version.
             *
             * @since 4.13
             */
            'wpra/core/version' => function (ContainerInterface $c) {
                return $c->get('wpra/core/info')['version'];
            },
            /*
             * The path to the WP RSS Aggregator main plugin file.
             *
             * @since 4.13
             */
            'wpra/core/plugin_file_path' => function () {
                return $this->pluginFilePath;
            },
            /*
             * The path to the WP RSS Aggregator plugin directory.
             *
             * @since 4.13
             */
            'wpra/core/plugin_dir_path' => function (ContainerInterface $c) {
                return plugin_dir_path($c->get('wpra/core/plugin_file_path'));
            },
            /*
             * The URL to the WP RSS Aggregator plugin directory.
             *
             * @since 4.13
             */
            'wpra/core/plugin_dir_url' => function (ContainerInterface $c) {
                return plugin_dir_url($c->get('wpra/core/plugin_file_path'));
            },
            /*
             * The path to the `includes` directory. Trailing slash is included.
             *
             * @since 4.13
             */
            'wpra/core/includes_dir_path' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_path') . 'includes/';
            },
            /*
             * The path to the `templates` directory. Trailing slash is included.
             *
             * @since 4.13
             */
            'wpra/core/templates_dir_path' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_path') . 'templates/';
            },
            /*
             * The URL to the directory where WP RSS Aggregator JS files can be found. Trailing slash is included.
             *
             * @since 4.13
             */
            'wpra/core/js_dir_url' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_url') . 'js/';
            },
            /*
             * The URL to the directory where WP RSS Aggregator CSS files can be found. Trailing slash is included.
             *
             * @since 4.13
             */
            'wpra/core/css_dir_url' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_url') . 'css/';
            },
            /*
             * The URL to the directory where WP RSS Aggregator images can be found. Trailing slash is included.
             *
             * @since 4.13
             */
            'wpra/core/images_dir_url' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_url') . 'images/';
            },
            /*
             * The minimum WordPress version required by WP RSS Aggregator.
             *
             * @since 4.13
             */
            'wpra/core/min_wp_version' => function () {
                return WPRSS_WP_MIN_VERSION;
            },
            /*
             * The minimum PHP version required by WP RSS Aggregator.
             *
             * @since 4.13
             */
            'wpra/core/min_php_version' => function () {
                return WPRSS_MIN_PHP_VERSION;
            },
            /*
             * The current version of the WP RSS Aggregator database.
             *
             * @since 4.13
             */
            'wpra/core/db_version' => function () {
                return WPRSS_DB_VERSION;
            },
            /*
             * The WP RSS Aggregator changelog.
             *
             * @since 4.13
             */
            'wpra/core/changelog' => function (ContainerInterface $c) {
                $file = $c->get('wpra/core/changelog_file_path');
                $raw = file_get_contents($file);

                return $raw;
            },
            /*
             * The WP RSS Aggregator changelog, in data set form.
             *
             * @since 4.13
             */
            'wpra/core/changelog_dataset' => function (ContainerInterface $c) {
                return new ChangelogDataSet($c->get('wpra/core/changelog_file_path'));
            },
            /*
             * The path to the WP RSS Aggregator changelog file.
             *
             * @since 4.13
             */
            'wpra/core/changelog_file_path' => function (ContainerInterface $c) {
                return $c->get('wpra/core/plugin_dir_path') . 'CHANGELOG.md';
            },
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
        do_action('wpra/init');
    }
}
