<?php

namespace RebelCode\Wpra\Core\Modules\FeedTemplates\Handlers;

/**
 * The handler that hides template content from the public-facing side unless a nonce is given.
 *
 * @since [*next-version*]
 */
class HidePublicTemplateContentHandler
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
     * The name of the nonce to use for allowing template content to be shown.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $nonce;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $cpt   The name of the templates CPT.
     * @param string $nonce The name of the nonce to use for allowing template content to be shown.
     */
    public function __construct($cpt, $nonce)
    {
        $this->cpt = $cpt;
        $this->nonce = $nonce;
    }

    public function __invoke()
    {
        global $post;

        if (is_admin() || is_feed() || wp_doing_cron() || wp_doing_ajax()) {
            return;
        }

        if (!is_object($post) || $post->post_type !== $this->cpt) {
            return;
        }

        check_admin_referer($this->nonce);
    }
}
