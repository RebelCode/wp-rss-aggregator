<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

use Dhii\Output\TemplateInterface;
use stdClass;
use Traversable;

/**
 * A handler that simply renders a template, either with a preset context or the handler's arguments.
 *
 * @since 4.13
 */
class RenderTemplateHandler
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
     * The template context or a callback that receives the handler's arguments and returns the template context.
     *
     * @since 4.13
     *
     * @var array|stdClass|Traversable
     */
    protected $context;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param TemplateInterface $template The template to render.
     * @param array|callable    $context  The template context or a callback that receives the handler's arguments
     *                                    and returns the template context.
     */
    public function __construct(TemplateInterface $template, $context = [])
    {
        $this->template = $template;
        $this->context = $context;;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function __invoke()
    {
        $ctx = is_callable($this->context)
            ? call_user_func_array($this->context, func_get_args())
            : $this->context;

        return $this->template->render($ctx);
    }
}
