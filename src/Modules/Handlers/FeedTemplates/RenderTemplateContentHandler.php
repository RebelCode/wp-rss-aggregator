<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates;

use Dhii\Output\TemplateInterface;

/**
 * The handler that renders the template content, by rendering the template itself as would be done through normal
 * means in WP RSS Aggregator.
 *
 * @since 4.13
 */
class RenderTemplateContentHandler
{
    /**
     * The name of the templates CPT.
     *
     * @since 4.13
     *
     * @var string
     */
    protected $cpt;

    /**
     * The master template to use for rendering.
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
     * @param string            $cpt      The name of the templates CPT.
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
     * @since 4.13
     */
    public function __invoke($content)
    {
        global $post;

        if ($post->post_type !== $this->cpt) {
            return $content;
        }

        return $this->template->render([
            'template' => $post->post_name,
        ]);
    }
}
