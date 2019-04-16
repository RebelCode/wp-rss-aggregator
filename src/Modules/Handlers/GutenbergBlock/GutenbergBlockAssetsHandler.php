<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\GutenbergBlock;

use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;

/**
 * Class for registering assets for gutenberg block.
 *
 * @since [*next-version*]
 */
class GutenbergBlockAssetsHandler
{
    /**
     * Templates collection.
     *
     * @since [*next-version*]
     *
     * @var CollectionInterface
     */
    protected $templates;

    /**
     * GutenbergBlockAssetsHandler constructor.
     *
     * @param CollectionInterface $templates Templates collection.
     */
    public function __construct(CollectionInterface $templates)
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
        wp_enqueue_script('wpra-shortcode', WPRSS_JS . 'gutenberg-block.min.js', [
            'wp-blocks',
            'wp-i18n',
            'wp-element',
            'wp-editor',
        ]);

        wp_enqueue_style('wpra-shortcode', WPRSS_CSS . 'gutenberg-block.min.css', [
            'wp-blocks',
        ]);

        $templates = [];
        foreach ($this->templates as $template) {
            $templates[] = [
                'label' => $template['name'],
                'value' => $template['slug']
            ];
        }

        wp_localize_script('wpra-shortcode', 'WPRA_BLOCK', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'templates' => $templates,
        ]);
    }
}
