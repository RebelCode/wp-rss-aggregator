<?php

namespace RebelCode\Wpra\Core\Modules\FeedTemplates\Handlers;

use Dhii\Output\TemplateInterface;

/**
 * The handler that renders the template content, by rendering the template itself as would be done through normal
 * means in WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class RenderTemplateContentHandler
{
    /**
     * The name of the templates CPT.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $cpt;

    /**
     * The master template to use for rendering.
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
     * @param string            $cpt The name of the templates CPT.
     * @param TemplateInterface $template The master template to use for rendering.
     */
    public function __construct($cpt, TemplateInterface $template)
    {
        $this->cpt = $cpt;
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke($content)
    {
        global $post;

        if ($post->post_type !== $this->cpt) {
            return $content;
        }

        return $this->template->render([
            'template' => $post->post_name
        ]);
    }
}
