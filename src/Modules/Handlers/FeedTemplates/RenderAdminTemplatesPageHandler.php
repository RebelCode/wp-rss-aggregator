<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates;

use Dhii\Output\RendererInterface;
use RebelCode\Wpra\Core\Wp\Asset\AssetInterface;

/**
 * The handler that renders the admin feed templates page.
 *
 * @since 4.13
 */
class RenderAdminTemplatesPageHandler
{
    /**
     * The list of assets required to render the page.
     *
     * @since [*next-version*]
     *
     * @var AssetInterface[]
     */
    protected $assets;

    /**
     * The list of states to render on the templates admin page.
     *
     * @since [*next-version*]
     *
     * @var RendererInterface[]
     */
    protected $states;

    /**
     * RenderAdminTemplatesPageHandler constructor.
     *
     * @param AssetInterface[] $assets The list of assets required to render the page.
     * @param RendererInterface[] $states
     */
    public function __construct($assets, $states)
    {
        $this->assets = $assets;
        $this->states = $states;
    }

    /**
     * @since 4.13
     */
    public function __invoke()
    {
        foreach ($this->assets as $asset) {
            $asset->enqueue();
        }

        foreach ($this->states as $state) {
            $state->render();
        }

        echo wprss_render_template('admin/templates-page.twig', [
            'title' => 'Templates',
            'subtitle' => 'Follow these introductory steps to get started with WP RSS Aggregator.',
        ]);
    }
}
