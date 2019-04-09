<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\GuttenbergBlock;

use RebelCode\Wpra\Core\Data\AbstractDataSet;

/**
 * Class for registering assets for guttenberg block.
 *
 * @since [*next-version*]
 */
class GuttenbergBlockAssetsHandler
{
    /**
     * Templates collection.
     *
     * @since [*next-version*]
     *
     * @var AbstractDataSet
     */
    protected $templates;

    /**
     * GuttenbergBlockAssetsHandler constructor.
     *
     * @param AbstractDataSet $templates Templates collection.
     */
    public function __construct($templates)
    {
        $this->templates = $templates;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        wp_enqueue_script('wpra-shortcode', WPRSS_JS . 'guttenberg-block.min.js', [
            'wp-blocks',
            'wp-i18n',
            'wp-element',
            'wp-editor',
        ]);

        wp_enqueue_style('wpra-shortcode', WPRSS_CSS . 'guttenberg-block.min.css', [
            'wp-blocks',
        ]);

        $templates = [];
        foreach ($this->templates as $template) {
            $templates[] = [
                'label' => $template['name'],
                'value' => ($template['type'] === '__built_in') ? '' : $template['slug']
            ];
        }

        wp_localize_script('wpra-shortcode', 'WRPA_BLOCK', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'templates' => $templates,
        ]);
    }
}
