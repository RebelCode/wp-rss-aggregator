<?php

if (!defined('ABSPATH')) {
    die;
}

/**
 * Detects an activation and redirects the user to the intro page.
 *
 * @since [*next-version*]
 */
add_action('admin_init', function () {
    $page = trim($_SERVER["REQUEST_URI"], '/');
    $isPluginsPage = strpos($page, 'plugins.php') !== false;

    if (!$isPluginsPage) {
        return;
    }

    add_action('admin_footer', function () {
        wprss_plugin_enqueue_app_scripts('wpra-plugins', WPRSS_JS . 'plugins.min.js', [], '0.1', true);
        wp_enqueue_style('wpra-plugins', WPRSS_CSS . 'plugins.min.css');

        $addons = wprss_find_installed_addon_names();
        $addons = array_fill_keys($addons, 1);

        wp_localize_script('wpra-plugins', 'WrpaDisablePoll', [
            'url' => 'https://hooks.zapier.com/hooks/catch/305784/puf5uf/',
            'model' => [
                'reason' => 'Other',
                'follow_up' => null,
                'date' => date('j M Y'),
                'addons' => $addons,
            ],
            'form' => [
                [
                    'label' => '',
                    'type' => 'radio',
                    'name' => 'reason',
                    'options' =>
                        [
                            [
                                'value' => 'I no longer need the plugin',
                                'label' => __('I no longer need the plugin', WPRSS_TEXT_DOMAIN),
                            ],
                            [
                                'value' => 'I found a better alternative',
                                'label' => __('I found a better alternative', WPRSS_TEXT_DOMAIN),
                            ],
                            [
                                'value' => 'I couldn\'t get the plugin to work',
                                'label' => __('I couldn\'t get the plugin to work', WPRSS_TEXT_DOMAIN),
                            ],
                            [
                                'value' => 'I\'m temporarily deactivating the plugin, but I\'ll be back',
                                'label' => __('I\'m temporarily deactivating the plugin, but I\'ll be back', WPRSS_TEXT_DOMAIN),
                            ],
                            [
                                'value' => 'I have a WP RSS Aggregator add-on',
                                'label' => __('I have a WP RSS Aggregator add-on', WPRSS_TEXT_DOMAIN),
                            ],
                            [
                                'value' => 'Other',
                                'label' => __('Other', WPRSS_TEXT_DOMAIN),
                            ],
                        ],
                ],
                [
                    'label' => __('Would you mind sharing its name?', WPRSS_TEXT_DOMAIN),
                    'type' => 'textarea',
                    'name' => 'follow_up',
                    'condition' =>
                        [
                            'field' => 'reason',
                            'operator' => '=',
                            'value' => 'I found a better alternative',
                        ],
                ],
                [
                    'type' => 'content',
                    'label' => __('Have you <a target="_blank" href="https://wordpress.org/support/plugin/wp-rss-aggregator/">contacted our support team</a> or checked out our <a href="https://kb.wprssaggregator.com/" target="_blank">Knowledge Base</a>?', WPRSS_TEXT_DOMAIN),
                    'condition' =>
                        [
                            'field' => 'reason',
                            'operator' => '=',
                            'value' => 'I couldn\'t get the plugin to work',
                        ],
                ],
                [
                    'type' => 'content',
                    'label' => __('This core plugin is required for all our premium add-ons. Please don\'t deactivate it if you currently have premium add-ons installed and activated.', WPRSS_TEXT_DOMAIN),
                    'condition' =>
                        [
                            'field' => 'reason',
                            'operator' => '=',
                            'value' => 'I have a WP RSS Aggregator add-on',
                        ],
                ],
                [
                    'label' => __('Please share your reason...', WPRSS_TEXT_DOMAIN),
                    'type' => 'textarea',
                    'name' => 'follow_up',
                    'condition' =>
                        [
                            'field' => 'reason',
                            'operator' => '=',
                            'value' => 'Other',
                        ],
                ],
            ]
        ]);

        echo '<div id="wpra-plugins-app"></div>';
    });
});
