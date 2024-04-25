<?php

add_action('admin_notices', function () {
    if (wprss_v5_is_available()) {
        wprss_v5_available_notice();
    } else {
        wprss_v5_coming_soon_notice();
    }
});

add_action('wp_ajax_wprss_dismiss_v5_notice', function () {
    $nonce = $_REQUEST['nonce'];
    $nonceOk = wp_verify_nonce($nonce, 'wpra-dismiss-v5-notice');
    if (!$nonceOk) {
        die('Not allowed');
    }

    $noticeId = trim($_REQUEST['notice'] ?? '');
    if (empty($noticeId)) {
        die('Empty notice ID');
    }

    switch ($noticeId) {
        case 'wprss_v5_coming_soon':
            update_option('wprss_v5_coming_notice_dismissed', '1');
            break;
        case 'wprss_v5_available':
            update_option('wprss_v5_available_dismissed', '1');
            break;
        default:
            die('Invalid notice ID');
    }

    die("OK");
});

add_filter('in_plugin_update_message-wp-rss-aggregator/wp-rss-aggregator.php', function () {
    if (!wprss_v5_is_available()) {
        return;
    }
    ?>
        <br>
        <span style="line-height: 24px;">
            <span style="display: inline-block; width: 24px;"></span>
            <b><?= __('Note:') ?></b>
            <span>
                <?= __('This is a major update. Prior testing on a staging site is strongly recommended.', 'wprss') ?>
            </span>
        </span>
    <?php
});

add_filter('site_transient_update_plugins', function ($updates) {
    if (!wprss_v5_contains_update($updates)) {
        return $updates;
    }

    $msg = __('This is a major update. Prior testing on a staging site is strongly recommended.', 'wprss');

    $basename = plugin_basename(WPRSS_FILE_CONSTANT);
    $updates->response[$basename]->upgrade_notice = $msg;

    return $updates;
});

function wprss_v5_is_available()
{
    $updates = get_site_transient('update_plugins');
    return wprss_v5_contains_update($updates);
}

function wprss_v5_contains_update($updates)
{
    $basename = plugin_basename(WPRSS_FILE_CONSTANT);
    if (!is_object($updates) && !isset($updates->response[$basename])) {
        return false;
    }

    $wprssUpdate = $updates->response[$basename] ?? null;

    if (!is_object($wprssUpdate) || !isset($wprssUpdate->new_version)) {
        return false;
    }

    if (version_compare($wprssUpdate->new_version, '5.0', '<')) {
        return false;
    }

    return true;
}

function wprss_v5_coming_soon_notice()
{
    $dismissed = get_option('wprss_v5_coming_notice_dismissed', '0');
    $dismissed = filter_var($dismissed, FILTER_VALIDATE_BOOLEAN);
    if ($dismissed) {
        return;
    }

    echo wprss_v5_notice_render(
        'wprss_v5_coming_soon',
        __('Exciting news for Aggregator!', 'wprss'),
        sprintf(
            _x(
                'Our highly-anticipated update is coming soon. This major update will require your website to be running PHP 7.4 or higher. To learn more about v5.0 %s',
                '%s = "click here" link',
                'wprss'
            ),
            sprintf(
                '<a href="%s" target="_blank">%s</a>',
                'https://www.wprssaggregator.com/v5-update/',
                __('click here', 'wprss'),
            )
        ),
    );
}

function wprss_v5_available_notice()
{
    $dismissed = get_option('wprss_v5_available_dismissed', '0');
    $dismissed = filter_var($dismissed, FILTER_VALIDATE_BOOLEAN);
    if ($dismissed) {
        return;
    }

    echo wprss_v5_notice_render(
        'wprss_v5_available',
        __('A major update of Aggregator is available.', 'wprss'),
        sprintf(
            _x(
                '%s to get access to the new and improved aggregator.',
                '%s = "Update" link',
                'wprss'
            ),
            sprintf(
                '<a href="%s">%s</a>',
                admin_url('update-core.php'),
                __('Update', 'wprss'),
            )
        ),
    );
}

function wprss_v5_notice_render($id, $title, $content)
{
    $icon = WPRSS_IMG . 'wpra-icon-transparent-new.png';
    $nonce = wp_create_nonce('wpra-dismiss-v5-notice');

    ob_start();
    ?>
        <div id="<?= esc_attr($id) ?>" class="notice wpra-v5-notice" data-notice-id="<?= esc_attr($id) ?>"> 
            <input type="hidden" class="wpra-v5-notice-nonce" value="<?= esc_attr($nonce) ?>" />

            <div class="wpra-v5-notice-left">
                <img src="<?= esc_attr($icon) ?>" style="width: 32px !important" alt="WP RSS Aggregator" />
            </div>
            <div class="wpra-v5-notice-right">
                <h3><?= $title ?></h3>
                <p>
                    <?= $content ?>
                </p>
            </div>
            <button class="wpra-v5-notice-close">
                <span class="dashicons dashicons-no-alt" />
            </button>
        </div>
    <?php

    return ob_get_clean();
}
