<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!WPRA_V5_USE_V4) {
    return;
}

add_action('wprss_add_settings_fields_sections', function ($tab) {
    if ($tab === 'switch_to_v5') {
        settings_fields('wprss_enable_v5_group');
        do_settings_sections('wprss_enable_v5_group');
    }
});

add_filter('wprss_options_tabs_final', function ($tabs) {
    $tabs[] = [
        'label' => __('Switch to v5', 'wprss'),
        'slug' => 'switch_to_v5',
    ];
    return $tabs;
});

add_action('admin_init', function () {
    register_setting('wprss_enable_v5_group', 'wprss_enable_v5', [
        'default' => '0',
    ]);

    add_settings_section(
        'wprss_enable_v5_section',
        __('Switch to v5', 'wprss'),
        function () {
            echo '<p>' . __('Ready to switch to v5? Click the below button to start using the new Aggregator! You can switch back to v4 later.', 'wprss') . '</p>';
            echo '<script type="text/javascript">';
            echo '  document.addEventListener("DOMContentLoaded", function () {';
            echo '    document.querySelector("p.submit")?.remove();';
            echo '  })';
            echo '</script>';
            echo '<input type="hidden" value="1" name="wprss_enable_v5" />';
            echo '<button type="submit" class="button button-primary">' . esc_html__('Switch to v5', 'wprss') . '</button>';
        },
        'wprss_enable_v5_group'
    );
});

function wprss_redirect_to_v5($prevValue, $newValue)
{
    if (strval($newValue) === '1') {
        wp_redirect(admin_url('admin.php?page=aggregator'));
        exit;
    }
}

add_action('update_option_wprss_enable_v5', 'wprss_redirect_to_v5', 10, 2);
add_action('add_option_wprss_enable_v5', 'wprss_redirect_to_v5', 10, 2);
