<?php

namespace RebelCode\Wpra\Core\Modules\FeedSources;

use Dhii\Output\TemplateInterface;

class RenderFeedSourceContentHandler
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
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke($content)
    {
        if (get_post_type() === 'wprss_feed' && !is_feed()) {
            return $this->template->render([
                'query_source' => get_the_ID(),
            ]);
        }

        return $content;
    }
}
