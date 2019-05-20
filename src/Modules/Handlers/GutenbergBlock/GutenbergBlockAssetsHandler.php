<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\GutenbergBlock;

use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;
use RebelCode\Wpra\Core\Wp\Asset\AssetInterface;

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
     * The list of assets required to render the block.
     *
     * @since [*next-version*]
     *
     * @var AssetInterface[]
     */
    protected $assets;

    /**
     * GutenbergBlockAssetsHandler constructor.
     *
     * @param AssetInterface[] $assets The list of assets for the block.
     * @param CollectionInterface $templates Templates collection.
     */
    public function __construct(array $assets, CollectionInterface $templates)
    {
        $this->assets = $assets;
        $this->templates = $templates;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function __invoke()
    {
        foreach ($this->assets as $asset) {
            $asset->enqueue();
        }

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
