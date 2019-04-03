<?php

namespace RebelCode\Wpra\Core\Modules\FeedTemplates\Handlers;

use Dhii\Output\TemplateInterface;

/**
 * The handler for rendering a feeds template.
 *
 * @since [*next-version*]
 */
class AjaxRenderFeedsTemplateHandler
{
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
     * @param TemplateInterface $template the template to render.
     */
    public function __construct(TemplateInterface $template)
    {
        $this->template = $template;
    }

    /**
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $args = filter_input(
            INPUT_GET,
            'wprss_render_args',
            FILTER_DEFAULT,
            FILTER_REQUIRE_ARRAY | FILTER_NULL_ON_FAILURE
        );
        $args = is_array($args) ? $args : [];

        echo json_encode([
            'render' => $this->template->render($args)
        ]);

        die;
    }
}
