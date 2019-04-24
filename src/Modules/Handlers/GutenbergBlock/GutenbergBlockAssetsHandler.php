<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\GutenbergBlock;

use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;

/**
 * Class for registering assets for gutenberg block.
 *
 * @since 4.13
 */
class GutenbergBlockAssetsHandler
{
    /**
     * Templates collection.
     *
     * @since 4.13
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
     * @since 4.13
     */
    public function __invoke()
    {
        wp_enqueue_script('wpra-gutenberg-block', WPRSS_APP_JS . 'gutenberg-block.min.js', [
            'wp-blocks',
            'wp-i18n',
            'wp-element',
            'wp-editor',
        ]);

        wp_enqueue_style('wpra-gutenberg-block', WPRSS_APP_CSS . 'gutenberg-block.min.css');

        $templates = [];
        foreach ($this->templates as $template) {
            $templates[] = [
                'label' => $template['name'],
                'value' => $template['slug'],
                'limit' => isset($template['options']['limit']) ? $template['options']['limit'] : 15,
                'pagination' => isset($template['options']['pagination']) ? $template['options']['pagination'] : true,
            ];
        }

        wp_localize_script('wpra-gutenberg-block', 'WPRA_BLOCK', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'templates' => $templates,
            'is_et_active' => wprss_is_et_active(),
        ]);
    }
}
