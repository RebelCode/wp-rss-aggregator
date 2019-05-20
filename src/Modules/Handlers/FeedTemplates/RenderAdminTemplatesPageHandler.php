<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates;

use RebelCode\Wpra\Core\Wp\Asset\AssetInterface;

/**
 * The handler that renders the admin feed templates page.
 *
 * @since 4.13
 */
class RenderAdminTemplatesPageHandler
{
    /**
     * The feeds template model structure.
     *
     * @var array
     */
    protected $modelSchema;

    /**
     * Tooltips for feed template model fields.
     *
     * @var array
     */
    protected $modelTooltips;

    /**
     * Feed template's fields options.
     *
     * @var array
     */
    protected $templateOptions;

    /**
     * The list of JS modules to load.
     *
     * @var array
     */
    protected $modules;

    /**
     * The list of assets required to render the page.
     *
     * @since [*next-version*]
     *
     * @var AssetInterface[]
     */
    protected $assets;

    /**
     * RenderAdminTemplatesPageHandler constructor.
     *
     * @param AssetInterface[] $assets  The list of assets required to render the page.
     * @param array $modelTooltips   Tooltips for feed template model fields.
     * @param array $templateOptions Feed template's fields options.
     * @param array $modules         The list of JS modules to load.
     */
    public function __construct($assets, $modelSchema, $modelTooltips, $templateOptions, $modules)
    {
        $this->assets = $assets;
        $this->modelSchema = $modelSchema;
        $this->modelTooltips = $modelTooltips;
        $this->templateOptions = $templateOptions;
        $this->modules = $modules;
    }

    /**
     * @since 4.13
     */
    public function __invoke()
    {
        foreach ($this->assets as $asset) {
            $asset->enqueue();
        }

        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        wp_localize_script('wpra-templates', 'WpraGlobal', [
            'admin_base_url' => admin_url(),
            'templates_url_base' => str_replace($url, '', menu_page_url('wpra_feed_templates', false)),
            'is_existing_user' => !wprss_is_new_user(),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);

        wp_localize_script('wpra-templates', 'WpraTemplates', [
            'model_schema' => $this->modelSchema,
            'model_tooltips' => $this->modelTooltips,
            'options' => $this->templateOptions,
            'modules' => $this->modules,
            'base_url' => rest_url('/wpra/v1/templates'),
        ]);

        echo wprss_render_template('admin/templates-page.twig', [
            'title' => 'Templates',
            'subtitle' => 'Follow these introductory steps to get started with WP RSS Aggregator.',
        ]);
    }
}
