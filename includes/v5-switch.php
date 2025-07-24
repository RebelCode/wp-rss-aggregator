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
            <div class="wprss-v5-upgrade-wrapper">

                <div class="wprss-section" style="text-align: center;">
                    <img
                        src="<?php echo esc_attr(WPRSS_IMG . 'wpra-icon-transparent-new.png') ?>"
                        alt="WP RSS Aggregator logo"
                        style="width: 50px; height: 50px;"
                    />
                    <h2 class="wprss-v5-title"><?php esc_html_e('Aggregator v5 is here!', 'wprss'); ?></h2>
                    <p class="wprss-v5-subtitle"><?php esc_html_e('Get the latest version with faster performance and a sleek new look', 'wprss'); ?></p>

                    <div class="wprss-v5-hero-video-box">
                        <div class="wprss-v5-hero-video-wrapper">
                            <iframe 
                                width="100%" 
                                height="200" 
                                src="https://www.youtube.com/embed/bfiR3kx3OMs" 
                                title="<?php esc_attr_e( 'Welcome to Aggregator v5', 'wprss' ); ?>" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                allowfullscreen
                            ></iframe>
                        </div>
                        <p class="wprss-v5-video-caption"><?php esc_html_e( 'Welcome to Aggregator v5', 'wprss' ); ?><br />
                        <span><?php esc_html_e( 'Upgrade safely from version 4', 'wprss' ); ?></span></p>
                    </div>
                    
                    <div class="wprss-v5-videos">
                        <div class="wprss-v5-video-box">
                            <div class="wprss-v5-video-wrapper">
                                <iframe 
                                    width="100%" 
                                    height="200" 
                                    src="https://www.youtube.com/embed/DeUTFPADb1g" 
                                    title="<?php esc_attr_e( 'What’s New in v5', 'wprss' ); ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    allowfullscreen
                                ></iframe>
                            </div>
                            <p class="wprss-v5-video-caption"><?php esc_html_e( 'What’s New in v5', 'wprss' ); ?><br />
                                <span><?php esc_html_e( 'Discover the latest improvements', 'wprss' ); ?></span></p>
                        </div>

                        <div class="wprss-v5-video-box">
                            <div class="wprss-v5-video-wrapper">
                                <iframe 
                                    width="100%" 
                                    height="200" 
                                    src="https://www.youtube.com/embed/BrEKqGD_Lps" 
                                    title="<?php esc_attr_e( 'Migration Guide', 'wprss' ); ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    allowfullscreen
                                ></iframe>
                            </div>
                            <p class="wprss-v5-video-caption"><?php esc_html_e( 'Migration Guide', 'wprss' ); ?><br />
                                <span><?php esc_html_e( 'Step-by-step walkthrough from v4 to v5', 'wprss' ); ?></span></p>
                        </div>
                    </div>

                </div>

                <div class="wprss-section wprss-feature-list">
                    <h3>
                        <?php esc_html_e("We're excited to introduce Aggregator v5, but before you switch, here's what you need to know:", 'wprss'); ?>
                    </h3>
                    <ul class="wprss-v5-info-list">
                        <li>
                            <img class="feature-icon" src="<?php echo WPRSS_IMG . 'calendar.svg'; ?>" alt="calendar icon" />

                            <div class="feature-header">
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
                            </div>    
                        </li>
                        <li>
                            <img class="feature-icon" src="<?php echo WPRSS_IMG . 'star.svg'; ?>" alt="star icon" />

                            <div class="feature-header">
                                <h3>
                                    <?php esc_html_e('Using a Premium Plan?', 'wprss'); ?>
                                </h3>
                                <p>
                                <?php 
                                    printf(
                                        wp_kses(
                                            __(
                                                'To unlock the premium features in v5 and receive future updates, you’ll need an <strong><a href="%1$s" target="_blank" rel="noopener noreferrer">active plan license.</a></strong><br> If your plan has expired, please <a target="_blank" href="%2$s">renew or upgrade here</a> before migrating.',
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
                            </div>

                        </li>

                        <li>
                            <img class="feature-icon" src="<?php echo WPRSS_IMG . 'tool.svg'; ?>" alt="tool icon" />

                            <div class="feature-header">
                                <h3>
                                    <?php esc_html_e('Have Individual Add-Ons?', 'wprss'); ?>
                                </h3>
                                <p>
                                <?php 
                                    printf(
                                        wp_kses(
                                            __(
                                                'Previously bought single add-ons without a plan? Those are now considered <strong>legacy licenses.</strong><br> You’ll need to  <a target="_blank" href="%1$s">upgrade to any plan</a> (Basic, Plus, Pro, or Elite) to keep enjoying premium features, updates, and support.',
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
                            </div>
                        </li>
                        <li>
                            <img class="feature-icon" src="<?php echo WPRSS_IMG . 'key.svg'; ?>" alt="key icon" />

                            <div class="feature-header">
                                <h3>
                                    <?php esc_html_e('Free User?', 'wprss'); ?>
                                </h3>
                                <p>
                                <?php 
                                    printf(
                                        wp_kses(
                                            __(
                                                'You’re welcome to switch to v5 at no cost, but please note that <a target="_blank" href="%1$s"><strong>premium features require a plan license.</strong></a><br> You’ll need to  <a target="_blank" href="%2$s">upgrade to any plan</a> (Basic, Plus, Pro, or Elite) to unlock premium features and support.',
                                                'wprss'
                                            ),
                                            [ 'strong' => [], 'br' => [], 'a' => [
                                                'href' => [],
                                                'target' => [],
                                                'rel' => [],
                                            ], ]
                                        ),
                                        esc_url('https://www.wprssaggregator.com/pricing/'),
                                        esc_url('https://www.wprssaggregator.com/upgrade/'),
                                    );
                                ?>
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="wprss-section">
                    <h3><?php esc_html_e('Migration Tips', 'wprss'); ?></h3>
                    <ul style="list-style: decimal; padding-left: 20px; margin:25px 0;">
                    <li>
                        <?php
                        echo wp_kses(
                            __(
                                '<strong>Test first</strong> on a staging site.',
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
                                'You can <strong>roll back to v4</strong> on the <strong>Settings</strong> page in v5.',
                                'wprss'
                            ),
                            [ 'strong' => [] ]
                        );
                        ?>
                    </li>
                    <li>
                    <?php esc_html_e('Before migrating, we recommend backing up your site:', 'wprss'); ?>
                    </li>
                    <ul style="list-style: disc; padding-left: 20px;">
                        <li>
                        <?php
                        echo wp_kses(
                            __(
                                'Make sure you have your <strong>main license key</strong> (not add-on keys).',
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
                                '<strong>Ensure any add-ons you own are installed and activated.</strong>',
                                'wprss'
                            ),
                            [ 'strong' => [] ]
                        );
                        ?>
                    </ul>
                </ul>
                <div class="wprss-tip-note" style="display: flex; align-items: flex-start; gap: 10px; background-color:#FEF6EB; padding: 32px 40px 32px 24px;">
                        <img class="feature-icon" src="<?php echo WPRSS_IMG . 'alert.svg'; ?>" alt="alert icon" />
                        <p style="margin: 0;">
                        <?php 
                            printf(
                                wp_kses(
                                    __(
                                        '<strong>Note:</strong> If your site has <strong>over 10,000 imported items</strong> or you encounter issues during migration, we recommend using the <strong>WP-CLI Migration Method</strong> for a more controlled and reliable process. <a target="_blank" href="%1$s">View the guide.</a>',
                                        'wprss'
                                    ),
                                    [ 'strong' => [], 'br' => [], 'a' => [
                                        'href' => [],
                                        'target' => [],
                                        'rel' => [],
                                    ], ]
                                ),
                                esc_url('https://www.wprssaggregator.com/help/migration-wp-cli/'),
                            );
                            ?>
                        </p>
                    </div>
                </div>


                <div class="wprss-section" style="text-align: center;">
                    <h3><?php esc_html_e('Need a Hand?', 'wprss'); ?></h3>

                    <p class="wprss-v5-help">
                                <?php
                                printf(
                                    wp_kses(
                                        __(
                                            'Check out the <a target="_blank" href="%1$s">migration guide</a> or <a target="_blank" href="%2$s">contact our support team</a>, we’re always happy to help!',
                                            'wprss'
                                        ),
                                        [ 'strong' => [], 'br' => [], 'a' => [
                                            'href' => [],
                                            'target' => [],
                                            'rel' => [],
                                        ], ]
                                    ),
                                    esc_url('https://www.wprssaggregator.com/help/migration/'),
                                    esc_url('https://www.wprssaggregator.com/contact/'),
                                );
                                ?>
                    </p>
                </div>

                <div class="wprss-v5-footer">
                    <h3><?php esc_html_e('Ready to Move Forward?', 'wprss'); ?></h3>
                    <p><?php esc_html_e('Click below to start your migration to v5', 'wprss'); ?></p>
                    <input type="hidden" name="wprss_enable_v5" value="1" />
                    <button type="submit" class="button" style="padding: 6px 12px;">
                        <?php esc_html_e('Switch to v5', 'wprss'); ?>
                    </button>
                    <script type="text/javascript">
                        document.addEventListener('DOMContentLoaded', function () {
                            document.querySelector('p.submit')?.remove();
                        });
                    </script>
                </div>
            </div>
            <style>
            .wprss-section {
                background-color: #fff;
                padding: 30px;
                margin-top: 30px;
                border-radius: 17px;
            }
            .wprss-section h3{
                font-size: 14px !important;
                margin-top: 0px !important;
            }

            .wprss-feature-list li{
                list-style: none;
                display: flex;
                align-items: flex-start;
                gap: 12px;
                margin-bottom: 8px;
            }

            .wprss-v5-upgrade-wrapper {
                max-width: 900px;
            }

            .wprss-v5-title {
                font-size: 24px;
                margin-bottom: 5px;
            }

            .wprss-v5-subtitle {
                font-size: 14px;
                color: #666;
                margin-bottom: 20px;
            }

            .wprss-v5-videos {
                display: flex;
                gap: 24px;
                margin-bottom: 30px;
                flex-wrap: wrap;
            }
            .wprss-v5-hero-video-box {
                border: 1px solid #757575;
                margin: 40px auto;
                width: 352px;
                background-color: #FAFAFA;
                padding: 20px;
                border-radius: 10px;
            }

            .wprss-v5-hero-video-wrapper {
                position: relative;
                padding-bottom: 56.25%; /* 16:9 */
                height: 0;
                overflow: hidden;
                border-radius: 6px;
                margin-bottom: 10px;
                background-color: #000;
            }

            .wprss-v5-hero-video-wrapper iframe {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                border: none;
            }


            .wprss-v5-video-box {
                flex: 1 0 0;
                min-width: 300px;
                background-color: #FAFAFA;
                padding: 20px;
                border-radius: 10px;
            }

            .wprss-v5-video-wrapper {
                position: relative;
                padding-bottom: 56.25%; /* 16:9 aspect ratio */
                height: 0;
                overflow: hidden;
                border-radius: 6px;
                margin-bottom: 10px;
                background-color: #000;
            }

            .wprss-v5-video-wrapper iframe {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                border: none;
            }

            .wprss-v5-video-caption {
                font-weight: bold;
                font-size: 14px;
                text-align: center;
            }

            .wprss-v5-video-caption span {
                display: block;
                font-weight: normal;
                font-size: 13px;
                color: #666;
            }
            .wprss-v5-info-list {
                list-style: disc;
                margin-top: 25px;

            }
            .wprss-v5-info-list li {
                margin-bottom: 20px;
            }

            .wprss-v5-migration-tips {
                list-style: decimal;
                padding-left: 20px;
                margin-bottom: 20px;
            }

            .wprss-v5-migration-tips ul {
                list-style: disc;
                padding-left: 20px;
                margin-top: 5px;
            }

            .wprss-v5-note {
                background: #fef7e5;
                border-left: 4px solid #ffc107;
                padding: 10px 15px;
                margin-bottom: 30px;
            }

            .wprss-v5-help {
                font-size: 14px;
                margin-top: 20px !important;
            }

            .wprss-v5-footer {
                background: #0D1759;
                color: #fff;
                padding: 30px;
                border-radius: 6px;
                text-align: center;
                margin-top: 30px;
            }

            .wprss-v5-footer h3 {
                color: #fff;
                font-size: 20px !important;
                font-weight: 400;
                margin-top: 5px !important;
            }

            .wprss-v5-footer p {
                color: #cdd4f2;
                margin-bottom: 20px;
            }

            </style>
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
