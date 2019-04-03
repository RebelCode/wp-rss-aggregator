<?php

namespace RebelCode\Wpra\Core\Modules\FeedTemplates\Handlers;

/**
 * The handler that renders the admin feed templates page.
 *
 * @since [*next-version*]
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
     * Feed template's fields options.
     *
     * @var array
     */
    protected $templateOptions;

    /**
     * RenderAdminTemplatesPageHandler constructor.
     *
     * @param array $modelSchema The feeds template model structure.
     * @param array $templateOptions  Feed template's fields options.
     */
    public function __construct($modelSchema, $templateOptions)
    {
        $this->modelSchema = $modelSchema;
        $this->templateOptions = $templateOptions;
    }

    /**
     * @since [*next-version*]
     */
    public function __invoke()
    {
        wprss_plugin_enqueue_app_scripts('wpra-templates', WPRSS_JS . 'templates.min.js', array(), '0.1', true);
        wp_enqueue_style('wpra-templates', WPRSS_CSS . 'templates.min.css');

        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        wp_localize_script('wpra-templates', 'WpraGlobal', [
            'admin_base_url' => admin_url(),
            'templates_url_base' => str_replace($url, '', menu_page_url('wpra_feed_templates', false)),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);

        wp_localize_script('wpra-templates', 'WpraTemplates', [
            'model_schema' => $this->modelSchema,
            'options' => $this->templateOptions,
            'base_url' => rest_url('/wpra/v1/templates'),
        ]);

        echo wprss_render_template('admin/templates-page.twig', array(
            'title' => 'Templates',
            'subtitle' => 'Follow these introductory steps to get started with WP RSS Aggregator.',
        ));
    }
}