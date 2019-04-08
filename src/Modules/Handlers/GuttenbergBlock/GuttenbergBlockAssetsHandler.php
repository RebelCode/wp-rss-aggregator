<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\GuttenbergBlock;

/**
 * Class for registering assets for guttenberg block.
 *
 * @since [*next-version*]
 */
class GuttenbergBlockAssetsHandler
{
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

        wp_localize_script('wpra-shortcode', 'WRPA_BLOCK', [
            'ajax_url' => admin_url('admin-ajax.php')
        ]);
    }
}
