<?php

namespace RebelCode\Wpra\Core\Shortcodes;

use Dhii\Output\TemplateInterface;
use RebelCode\Wpra\Core\Data\DataSetInterface;

/**
 * The feeds shortcode handler.
 *
 * @since [*next-version*]
 */
class FeedsShortcode
{
    /**
     * The settings dataset.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $settings;

    /**
     * The template to render.
     *
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $template;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface  $settings The settings dataset.
     * @param TemplateInterface $template The template to render.
     */
    public function __construct(DataSetInterface $settings, TemplateInterface $template)
    {
        $this->settings = $settings;
        $this->template = $template;
    }

    /**
     * @since [*next-version*]
     *
     * @param array $args The shortcode arguments.
     *
     * @return string The rendered shortcode result.
     */
    public function __invoke($args = [])
    {
        // Enqueue scripts
        wp_enqueue_script('jquery.colorbox-min', WPRSS_JS . 'jquery.colorbox-min.js', ['jquery']);
        wp_enqueue_script('wprss_custom', WPRSS_JS . 'custom.js', ['jquery', 'jquery.colorbox-min']);

        wp_enqueue_script('wpra-manifest', WPRSS_JS . 'wpra-manifest.min.js', ['jquery']);

        wp_enqueue_script('wpra-pagination', WPRSS_JS . 'pagination.min.js', ['wpra-manifest']);
        wp_enqueue_style('wpra-pagination', WPRSS_CSS . 'pagination.min.css');

        wp_localize_script('wpra-pagination', 'WpraPagination', [
            'baseUri' => rest_url('/wpra/v1/templates/render/'),
        ]);

        // Decode HTML entities in the arguments
        $args = is_array($args) ? $args : [];
        $args = array_map('html_entity_decode', $args);
        // Render the template
        $result = $this->template->render($args);

        // Filter the result and return it
        return apply_filters('wprss_shortcode_output', $result);
    }
}
