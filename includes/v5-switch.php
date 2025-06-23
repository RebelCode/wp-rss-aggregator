<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!WPRA_V5_USE_V4) {
    return;
}

add_action(
    'wprss_add_settings_fields_sections', function ($tab) {
        if ($tab === 'switch_to_v5') {
            settings_fields('wprss_enable_v5_group');
            do_settings_sections('wprss_enable_v5_group');
        }
    }
);

add_filter(
    'wprss_options_tabs_final', function ($tabs) {
        $tabs[] = [
        'label' => __('Switch to v5', 'wprss'),
        'slug' => 'switch_to_v5',
        ];
        return $tabs;
    }
);

add_action(
    'admin_init', function () {
        register_setting(
            'wprss_enable_v5_group', 'wprss_enable_v5', [
            'default' => '0',
            ]
        );

        add_settings_section(
            'wprss_enable_v5_section',
            __('', 'wprss'),
            function () {
                ?>
            <div style="max-width:800px; margin-top:20px;">

                <div style="
                    display: flex;
                    padding: 20px 25px;
                    align-items: center;
                    gap: 10px;
                    align-self: stretch;
                    border-radius: 17px;
                    background: #FFF;
                    ">
                    <img
                        src="<?php echo esc_attr(WPRSS_IMG . 'wpra-icon-transparent-new.png') ?>"
                        alt="WP RSS Aggregator logo"
                        style="width: 38px; height: 38px;"
                    />
                    <h3 style="margin: 0;">
                        <?php esc_html_e('Aggregator v5 is here!', 'wprss'); ?>
                    </h3>
                </div>

                <h3>
                    <strong>⚠ <?php esc_html_e('Important - read before migrating', 'wprss'); ?></strong>
                </h3>

                <p>
                    <?php esc_html_e('We’re excited to introduce Aggregator v5, but before you switch, here’s what you need to know:', 'wprss'); ?>
                </p>

                <h3>
                    <?php esc_html_e('Version 4 & Legacy Add-Ons Retiring', 'wprss'); ?>
                </h3>
                <p>
                    <?php
                    echo wp_kses(
                        __(
                            'Aggregator v4 and all legacy premium add-ons will officially reach end-of-life on <strong>December 31, 2025.</strong><br>After this date, they’ll no longer receive updates, support, or be available for download.',
                            'wprss'
                        ),
                        [ 'strong' => [], 'br' => [] ]
                    );
                    ?>
                </p>

                <h3>
                    <?php esc_html_e('Using a Premium Plan?', 'wprss'); ?>
                </h3>
                <p>
                   <?php 
                    printf(
                        wp_kses(
                            __(
                                'To unlock the premium features in v5 and receive future updates, you’ll need an <strong><a href="%1$s" target="_blank" rel="noopener noreferrer">active plan license.</a></strong><br> If your plan has expired, please <a href="%2$s">renew or upgrade here</a> before migrating.',
                                'wprss'
                            ),
                            [ 'strong' => [], 'br' => [], 'a' => [
                                'href' => [],
                                'target' => [],
                                'rel' => [],
                            ], ]
                        ),
                        esc_url('https://www.wprssaggregator.com/help/locating-your-license-keys/'),
                        esc_url('https://www.wprssaggregator.com/account/')
                    );
                    ?>
                </p>

                <h3>
                    <?php esc_html_e('Have Individual Add-Ons?', 'wprss'); ?>
                </h3>

                <p>
                   <?php 
                    printf(
                        wp_kses(
                            __(
                                'Previously bought single add-ons without a plan? Those are now considered <strong>legacy licenses.</strong><br> You’ll need to  <a href="%1$s">upgrade to any plan</a> (Basic, Plus, Pro, or Elite) to keep enjoying premium features, updates, and support.',
                                'wprss'
                            ),
                            [ 'strong' => [], 'br' => [], 'a' => [
                                'href' => [],
                                'target' => [],
                                'rel' => [],
                            ], ]
                        ),
                        esc_url('https://www.wprssaggregator.com/account/upgrades/'),
                    );
                    ?>
                </p>

                <h3>
                    <?php esc_html_e('Free User?', 'wprss'); ?>
                </h3>
                <p>
                   <?php 
                    printf(
                        wp_kses(
                            __(
                                'You’re welcome to switch to v5 at no cost, but please note that <a href="%1$s"><strong>premium features require a plan license.</strong></a>',
                                'wprss'
                            ),
                            [ 'strong' => [], 'br' => [], 'a' => [
                                'href' => [],
                                'target' => [],
                                'rel' => [],
                            ], ]
                        ),
                        esc_url('https://www.wprssaggregator.com/pricing/'),
                    );
                    ?>
                </p>

                <h3>
                    <?php esc_html_e('What to Expect After Migration', 'wprss'); ?>
                </h3>
                <p>
                    <?php
                    echo wp_kses(
                        __(
                            'Once you migrate to version 5, your sources will start syncing in the background, including the total number of imported items.<br> This process may take a few hours to complete. Once updates begin, they’ll continue according to your selected schedule.',
                            'wprss'
                        ),
                        [ 'strong' => [], 'br' => [] ]
                    );
                    ?>
                </p>

                <hr>
                <h3>
                    <?php esc_html_e('Migration Tips:', 'wprss'); ?>
                </h3>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li>
                        <?php
                        echo wp_kses(
                            __(
                                '<strong>Test first</strong> on a staging site if you can.',
                                'wprss'
                            ),
                            [ 'strong' => [] ]
                        );
                        ?>
                    </li>
                    <li>
                        <?php
                        echo wp_kses(
                            __(
                                'You can <strong>roll back to v4</strong> via the plugin or by downloading it again from your account.',
                                'wprss'
                            ),
                            [ 'strong' => [] ]
                        );
                        ?>
                    </li>
                    <li>
                    <?php esc_html_e('Before starting:', 'wprss'); ?>
                    </li>
                    <ul style="list-style: disc; padding-left: 20px;">
                        <li><?php esc_html_e('Make sure you have your main license key (not add-on keys).', 'wprss'); ?></li>
                        <li><?php esc_html_e('Ensure any add-ons you own are installed and activated.', 'wprss'); ?></li>
                    </ul>
                </ul>

                <h3>
                    <?php esc_html_e('Need a Hand?', 'wprss'); ?>
                </h3>
                <p>
                    <?php
                    echo sprintf(
                        wp_kses(
                            __('Check out the <a href="%1$s" target="_blank" rel="noopener noreferrer">migration guide</a> or <a href="%2$s" target="_blank" rel="noopener noreferrer">contact our support team</a>, we’re always happy to help!', 'wprss'),
                            [ 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ] ]
                        ),
                        esc_url('https://www.wprssaggregator.com/help/migration/'),
                        esc_url('https://www.wprssaggregator.com/contact/')
                    );
                    ?>
                    </p>

                <hr style="margin-top: 30px; margin-bottom: 20px;">

                <h3>
                    <?php esc_html_e('Ready to Move Forward?', 'wprss'); ?>
                </h3>
                <p>
                    <?php esc_html_e('Click below to start your migration to v5.', 'wprss'); ?>
                </p>
                </div>

                <input type="hidden" name="wprss_enable_v5" value="1" />
                <button type="submit" class="button button-primary">
                        <?php esc_html_e('Switch to v5', 'wprss'); ?>
                </button>
                <script type="text/javascript">
                    document.addEventListener('DOMContentLoaded', function () {
                        document.querySelector('p.submit')?.remove();
                    });
                </script>
                <?php
            },
            'wprss_enable_v5_group'
        );
    }
);


add_action('update_option_wprss_enable_v5', 'wprss_redirect_to_v5', 10, 2);
add_action('add_option_wprss_enable_v5', 'wprss_redirect_to_v5', 10, 2);
function wprss_redirect_to_v5($prevValue, $newValue)
{
    if ($newValue === '1') {
        set_transient('wprss_redirect_to_v5', '1', 60);
        wp_redirect(admin_url());
        exit;
    }
}
