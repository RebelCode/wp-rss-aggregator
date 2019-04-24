<?php

namespace RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates;

use Dhii\Output\TemplateInterface;
use RebelCode\Wpra\Core\RestApi\EndPoints\AbstractRestApiEndPoint;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The REST API endpoint for rendering templates.
 *
 * @since 4.13
 */
class RenderTemplateEndPoint extends AbstractRestApiEndPoint
{
    /**
     * The template to render.
     *
     * @since 4.13
     *
     * @var TemplateInterface
     */
    protected $template;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param TemplateInterface $template The template to render.
     */
    public function __construct(TemplateInterface $template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function handle(WP_REST_Request $request)
    {
        $args = $request->get_params();
        $args = is_array($args) ? $args : [];

        // Render the template
        $result = $this->template->render($args);

        // Filter the result and return it
        return new WP_REST_Response([
            'html' => apply_filters('wprss_shortcode_output', $result)
        ]);
    }
}
