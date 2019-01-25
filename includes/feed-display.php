<?php

use Aventura\Wprss\Core\Template\Api\FeedTemplateInterface;
use Aventura\Wprss\Core\Template\Api\TemplateContextFactoryInterface;
use Aventura\Wprss\Core\Template\FeedItemContextFactory;
use Aventura\Wprss\Core\Template\TemplateContextFactory;
use Aventura\Wprss\Core\Template\TemplateQueryFactory;
use Aventura\Wprss\Core\Template\TwigFeedTemplate;

/**
 * Feed display related functions
 *
 * @package WPRSSAggregator
 */

if (defined('WPRSS_USE_LEGACY_FEED_DISPLAY') && WPRSS_USE_LEGACY_FEED_DISPLAY) {
    require_once(WPRSS_INC . 'feed-display.php');
    die;
}

/**
 * This function builds the render context for rendering the feed items template.
 *
 * Firstly, WP RSS Aggregator's standard render context includes the feed items, so a query
 * must first be obtained via {@link wprss_get_feed_items_template_query()}.
 *
 * Secondly, it builds the builds the context using a {@link TemplateContextFactoryInterface}
 * instance. The default implementation used is the {@link TemplateContextFactory} class, which
 * requires a separate factory for creating the sub-contexts for each feed item yielded by the
 * query, as an instance of {@link FeedItemContextFactoryInterface}.
 *
 * @param array $args Optional render arguments.
 *
 * @return array The template context.
 */
function wprss_get_template_context(array $args = [])
{
    $query = wprss_get_template_query($args);
    $factory = wprss_get_template_context_factory($args);

    $context = $factory->make($query, $args);

    return apply_filters('wprss_template_context', $context, $args);
}

/**
 * Retrieves the template context factory to use for rendering feed items.
 *
 * The default implementation used is the {@link TemplateContextFactory} class, which requires a
 * separate factory for creating the sub-contexts for every feed item to be rendered, as an
 * instance of {@link FeedItemContextFactoryInterface}. The default implementation used for this
 * factory is the {@link FeedItemContextFactory}.
 *
 * @since [*next-version*]
 *
 * @param array $args Optional render arguments.
 *
 * @return TemplateContextFactoryInterface The template context factory.
 */
function wprss_get_template_context_factory(array $args = [])
{
    $itemFactory = apply_filters('wprss_template_item_context_factory', new FeedItemContextFactory(), $args);
    $contextFactory = apply_filters('wprss_template_context_factory', new TemplateContextFactory($itemFactory), $args);

    return $contextFactory;
}

/**
 * Retrieves the template query.
 *
 * @since [*next-version*]
 *
 * @param array $args The render arguments.
 *
 * @return WP_Query The query object.
 */
function wprss_get_template_query(array $args = [])
{
    $factory = apply_filters('wprss_template_query_factory', new TemplateQueryFactory(), $args);

    return $factory->make($args);
}

/**
 * Retrieves the template to use for rendering feed items.
 *
 * @since [*next-version*]
 *
 * @param array $args The render arguments.
 *
 * @return FeedTemplateInterface The template instance.
 */
function wprss_get_template(array $args = [])
{
    $templateName = (isset($args['template']))
        ? $args['template']
        : 'default';

    $templateFile = sprintf('feeds/%s/main.twig', $templateName);

    return new TwigFeedTemplate($templateFile);
}

/**
 * Renders imported feed items.
 *
 * @since [*next-version*]
 *
 * @param array $args Optional arguments for which items to render and how to render them.
 *
 * @return string The rendered result.
 */
function wprss_render($args = [])
{
    if (!is_array($args)) {
        $args = [];
    }

    // Transform the args into the template context
    $context = wprss_get_template_context($args);
    // Get the template
    $template = wprss_get_template($args);

    // Render the template with the context and return the result
    try {
        return $template->render($context);
    } catch (Exception $exception) {
        return sprintf(
            '<p>%s</p><p><pre>%s</pre></p>',
            __('Cannot show feed items. The following error occurred:', WPRSS_TEXT_DOMAIN),
            $exception->getMessage()
        );
    }
}

/**
 * Outputs rendered feed items on the front end.
 *
 * @since 2.0
 *
 * @param array $args Optional arguments for which items to render and how to render them.
 */
function wprss_display_feed_items($args = [])
{
    echo wprss_render($args);
}

/**
 * Redirects to wprss_display_feed_items
 * It is used for backwards compatibility to versions < 2.0
 *
 * @since 2.1
 */
function wp_rss_aggregator($args = [])
{
    wprss_display_feed_items($args);
}

add_action('wp_ajax_wprss_render', 'wp_render_ajax');
add_action('wp_ajax_nopriv_wprss_render', 'wp_render_ajax');

function wp_render_ajax() {
    $args = filter_input(INPUT_GET, 'wprss_render_args', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY | FILTER_NULL_ON_FAILURE);
    $args = is_array($args) ? $args : [];

    echo json_encode(['render' => wprss_render($args), 'page' => $args['page']]);
    die;
}

/**
 * Retrieve settings and prepare them for use in the display function
 *
 * @since 3.0
 */
function wprss_get_display_settings($settings = null)
{
    if ($settings === null) {
        $settings = get_option('wprss_settings_general');
    }
    // Parse the arguments together with their default values
    $args = wp_parse_args(
        $settings,
        [
            'open_dd' => 'blank',
            'follow_dd' => '',
        ]
    );

    // Prepare the 'open' setting - how to open links for feed items
    $open = '';
    switch ($args['open_dd']) {
        case 'lightbox' :
            $open = 'class="colorbox"';
            break;
        case 'blank' :
            $open = 'target="_blank"';
            break;
    }

    // Prepare the 'follow' setting - whether links marked as nofollow or not
    $follow = ($args['follow_dd'] == 'no_follow') ? 'rel="nofollow"' : '';

    // Prepare the final settings array
    $display_settings = [
        'open' => $open,
        'follow' => $follow,
    ];

    do_action('wprss_get_settings');

    return $display_settings;
}

/**
 * Generates an HTML link, using the saved display settings.
 *
 * @param string $link The link URL
 * @param string $text The link text to display
 * @param string $bool Optional boolean. If FALSE, the text is returned unlinked. Default: TRUE.
 *
 * @return string The generated link
 * @since 4.2.4
 */
function wprss_link_display($link, $text, $bool = true)
{
    $display_settings = wprss_get_display_settings(get_option('wprss_settings_general'));
    $a = $bool ? "<a {$display_settings['open']} {$display_settings['follow']} href='$link'>$text</a>" : $text;

    return $a;
}

add_filter('wprss_pagination', 'wprss_pagination_links');
/**
 * Display pagination links
 *
 * @since 3.5
 */
function wprss_pagination_links($output)
{
    // Get the general setting
    $pagination = wprss_get_general_setting('pagination');

    // Check the pagination setting, if using page numbers
    if ($pagination === 'numbered') {
        global $wp_query;
        $big = 999999999; // need an unlikely integer
        $output .= paginate_links([
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $wp_query->max_num_pages,
        ]);

        return $output;
    } // Otherwise, using default paginations
    else {
        $output .= '<div class="nav-links">';
        $output .= '    <div class="nav-previous alignleft">' . get_next_posts_link(__('Older posts',
                WPRSS_TEXT_DOMAIN)) . '</div>';
        $output .= '    <div class="nav-next alignright">' . get_previous_posts_link(__('Newer posts',
                WPRSS_TEXT_DOMAIN)) . '</div>';
        $output .= '</div>';

        return $output;
    }
}

add_filter('wprss_item_title', 'wprss_shorten_title', 10, 2);
add_filter('the_title', 'wprss_shorten_title', 10, 2);

/**
 * Checks the title limit option and shortens the title when necassary.
 *
 * @since 1.0
 */
function wprss_shorten_title($title, $id = null)
{
    if ($id === null) {
        return $title;
    }
    if ($id instanceof WP_Post) {
        $id = $id->ID;
    }

    // Get the option. If does not exist, use 0, which is ignored.
    $general_settings = get_option('wprss_settings_general');
    $title_limit = isset($general_settings['title_limit']) ? intval($general_settings['title_limit']) : 0;
    // Check if the title is for a wprss_feed_item, and check if trimming is needed
    if (isset($id) && get_post_type($id) === 'wprss_feed_item' && $title_limit > 0 && strlen($title) > $title_limit) {
        // Return the trimmed version of the title
        return substr($title, 0, $title_limit) . apply_filters('wprss_shortened_title_ending', '...');
    }

    // Otherwise, return the same title
    return $title;
}

/**
 * Limits a phrase/content to a defined number of words
 *
 * NOT BEING USED as we're using the native WP function, although the native one strips tags, so I'll
 * probably revisit this one again soon.
 *
 * @since  3.0
 *
 * @param  string  $words
 * @param  integer $limit
 * @param  string  $append
 *
 * @return string
 */
function wprss_limit_words($words, $limit, $append = '')
{
    /* Add 1 to the specified limit becuase arrays start at 0 */
    $limit = $limit + 1;
    /* Store each individual word as an array element
       up to the limit */
    $words = explode(' ', $words, $limit);
    /* Shorten the array by 1 because that final element will be the sum of all the words after the limit */
    array_pop($words);
    /* Implode the array for output, and append an ellipse */
    $words = implode(' ', $words) . $append;

    /* Return the result */

    return rtrim($words);
}
