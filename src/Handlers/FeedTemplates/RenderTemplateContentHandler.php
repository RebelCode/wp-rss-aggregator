<?php

namespace RebelCode\Wpra\Core\Handlers\FeedTemplates;

use Dhii\Output\TemplateInterface;
use RebelCode\Wpra\Core\Entities\Feeds\Templates\WpPostFeedTemplate;

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
    protected $masterTemplate;

    /**
     * The template to use for rendering previews.
     *
     * @since 4.14
     *
     * @var TemplateInterface
     */
    protected $previewTemplate;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param string            $cpt      The name of the templates CPT.
     * @param TemplateInterface $template The master template to use for rendering.
     * @param TemplateInterface $previewTemplate The template to use for rendering previews.
     */
    public function __construct($cpt, TemplateInterface $template, TemplateInterface $previewTemplate)
    {
        $this->cpt = $cpt;
        $this->masterTemplate = $template;
        $this->previewTemplate = $previewTemplate;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function __invoke($content)
    {
        global $post;

        // Check if current post type is a WPRA template
        if ($post->post_type !== $this->cpt) {
            return $content;
        }

        // Get the template instance
        $template = new WpPostFeedTemplate($post);

        // Check if this is a preview, determined by serialized options in the GET param
        $options = filter_input(INPUT_GET, 'options', FILTER_DEFAULT);
        if (!empty($options)) {
            // Unserialize the options
            $options = json_decode(base64_decode($options), true);
            $options = empty($options) ? [] : (array) $options;


            // Render the preview
            return $this->previewTemplate->render([
                'type' => $template['type'],
                'options' => $options
            ]);
        }

        // Render the template
        return $this->masterTemplate->render([
            'template' => $template['slug'],
        ]);
    }
}
