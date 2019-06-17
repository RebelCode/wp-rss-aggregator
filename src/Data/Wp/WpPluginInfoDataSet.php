<?php

namespace RebelCode\Wpra\Core\Data\Wp;

use RebelCode\Wpra\Core\Data\AbstractDelegateDataSet;
use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;

/**
 * A dataset implementation for WordPress plugin information.
 *
 * @since 4.13
 */
class WpPluginInfoDataSet extends AbstractDelegateDataSet
{
    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param string $pluginFilePath The path to the plugin's main file.
     */
    public function __construct($pluginFilePath)
    {
        parent::__construct($this->createInnerDataSet($pluginFilePath));
    }

    /**
     * Creates the inner data set.
     *
     * @since 4.13
     *
     * @param string $pluginFilePath The path to the plugin's main file.
     *
     * @return DataSetInterface
     */
    protected function createInnerDataSet($pluginFilePath)
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $inner = new ArrayDataSet(get_plugin_data($pluginFilePath));
        $aliased = new AliasingDataSet($inner, [
            'name' => 'Name',
            'title' => 'Title',
            'plugin_uri' => 'PluginURI',
            'version' => 'Version',
            'description' => 'Description',
            'author' => 'Author',
            'author_name' => 'AuthorName',
            'author_uri' => 'AuthorURI',
            'text_domain' => 'TextDomain',
            'domain_path' => 'DomainPath',
            'network' => 'Network',
            'sitewide' => '_sitewide',
        ]);

        return $aliased;
    }
}
