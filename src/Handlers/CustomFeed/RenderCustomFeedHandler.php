<?php

namespace RebelCode\Wpra\Core\Handlers\CustomFeed;

use Dhii\Output\TemplateInterface;
use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;

/**
 * The handler that renders the custom feed.
 *
 * @since 4.13
 */
class RenderCustomFeedHandler
{
    /**
     * @since [*next-version*]
     *
     * @var CollectionInterface
     */
    protected $items;

    /**
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $template;

    /**
     * Constructor.
     *
     * @todo Inject the required wp-options dataset, when it's available as a service
     *
     * @since [*next-version*]
     *
     * @param CollectionInterface $items    The items to include in the feed.
     * @param TemplateInterface   $template The template to use for rendering the feed.
     */
    public function __construct(CollectionInterface $items, TemplateInterface $template)
    {
        $this->items = $items;
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function __invoke()
    {
        $options = get_option('wprss_settings_general', []);

        $items = empty($options['custom_feed_limit'])
            ? $this->items
            : $this->items->filter(['posts_per_page' => $options['custom_feed_limit']]);

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header("$protocol 200 OK");
        // Send content header and start ATOM output
        header('Content-Type: application/rss+xml');
        // Disabling caching
        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
        header('Pragma: no-cache'); // HTTP 1.0.
        header('Expires: 0'); // Proxies.

        $title = wprss_get_general_setting('custom_feed_title');
        $context = [
            'charset' => get_option('blog_charset'),
            'title' => $title,
            'subtitle' => $title,
            'site_url' => trailingslashit(get_site_url()),
            'self_url' => trailingslashit(get_feed_link('wprss')),
            'updated_date' => date(DATE_ATOM),
            'generator' => [
                'name' => 'WP RSS Aggregator',
                'url' => 'https://wprssaggregator.com',
                'version' => WPRSS_VERSION,
            ],
            'items' => $items,
        ];

        echo $this->template->render($context);
    }
}
