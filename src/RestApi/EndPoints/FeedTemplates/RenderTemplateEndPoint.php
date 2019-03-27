<?php

namespace RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates;

use Dhii\Output\TemplateInterface;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\RestApi\EndPoints\AbstractRestApiEndPoint;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The REST API endpoint for rendering templates.
 *
 * @since [*next-version*]
 */
class RenderTemplateEndPoint extends AbstractRestApiEndPoint
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
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function handle(WP_REST_Request $request)
    {
        $args = $request->get_query_params();

        // Decode HTML entities in the arguments
        $args = is_array($args) ? $args : [];
        $args = array_map('html_entity_decode', $args);

        // Do not render pagination links for template rendered via endpoint.
        $args['pagination_visible'] = false;

        // Render the template
        $result = $this->template->render($args);

        // Filter the result and return it
        return new WP_REST_Response([
            'html' => apply_filters('wprss_shortcode_output', $result)
        ]);
    }
}
