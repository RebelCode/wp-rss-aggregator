<?php

namespace RebelCode\Wpra\Core\Modules\FeedTemplates\Handlers;

/**
 * The handler that detects a preview template request and redirects to the template's front-facing page.
 *
 * @since [*next-version*]
 */
class PreviewTemplateRedirectHandler
{
    /**
     * The name of the GET parameter to detect.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $getParam;

    /**
     * The name of the nonce to that allows template content to be shown on the public-facing side.
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
     * @param string $getArg The name of the GET parameter to detect.
     * @param string $nonce The name of the nonce to that allows template content to be shown on the public-facing side.
     */
    public function __construct($getArg, $nonce)
    {
        $this->getParam = $getArg;
        $this->nonce = $nonce;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        if (!is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed()) {
            return;
        }

        $previewId = filter_input(INPUT_GET, $this->getParam, FILTER_SANITIZE_STRING);
        if (empty($previewId)) {
            return;
        }

        if (!current_user_can('read_feed_template')) {
            wp_die(__('You do not have sufficient privileges!', 'wprss'));
            exit;
        }

        $permalink = get_permalink($previewId);
        if ($permalink === false) {
            wp_die(__('Invalid template ID', 'wprss'));
            exit;
        }

        $urlQuery = parse_url($permalink, PHP_URL_QUERY);
        $separator = (empty($urlQuery)) ? '?' : "&";
        $nonce = wp_create_nonce($this->nonce);
        $fullUrl = $permalink . $separator . '_wpnonce=' . $nonce;

        wp_safe_redirect($fullUrl);
        die;
    }
}
