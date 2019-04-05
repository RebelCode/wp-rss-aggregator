<?php

namespace RebelCode\Wpra\Core\Shortcodes;

use Dhii\Output\TemplateInterface;

/**
 * The feeds shortcode handler.
 *
 * @since [*next-version*]
 */
class FeedsShortcode
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
     * @param TemplateInterface $template The template to render.
     */
    public function __construct(TemplateInterface $template)
    {
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
        // Decode HTML entities in the arguments
        $args = is_array($args) ? $args : [];
        $args = array_map('html_entity_decode', $args);
        // Render the template
        $result = $this->template->render($args);

        // Filter the result and return it
        return apply_filters('wprss_shortcode_output', $result);
    }
}
