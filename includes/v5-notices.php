<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action(
    'admin_notices', function () {
        if (WPRA_V5_USE_V4) {
            wprss_v5_switch_notice();
        }
    }
);

add_action(
    'wp_ajax_wprss_dismiss_v5_notice', function () {
        $nonce = $_REQUEST['nonce'];
        $nonceOk = wp_verify_nonce($nonce, 'wpra-dismiss-v5-notice');
        if (!$nonceOk) {
            die('Not allowed');
        }

        $noticeId = trim($_REQUEST['notice'] ?? '');
        if (empty($noticeId)) {
            die('Empty notice ID');
        }

        update_option($noticeId . '_dismissed', '1');
        die("OK");
    }
);

add_filter(
    'in_plugin_update_message-wp-rss-aggregator/wp-rss-aggregator.php', function ($plugin_data, $response) {
        if (!wprss_v5_is_available()) {
            return '';
        }

        $migration_url = 'https://www.wprssaggregator.com/help-topics/v5-migration/';
        $plugin_slug = 'wp-rss-aggregator/wp-rss-aggregator.php';

        // Fallback URL in case automatic link generation fails
        $update_url = wp_nonce_url(
            self_admin_url("update.php?action=upgrade-plugin&plugin={$plugin_slug}"),
            "upgrade-plugin_{$plugin_slug}"
        );

        $html = '
        <br>
        <span style="line-height: 24px;">
            <span style="display: inline-block; width: 24px;"></span>
            <b>' . esc_html__('Note:', 'wprss') . '</b>
            <span>
                ' . sprintf(
                    // translators: 1: Link to migration guide, 2: Update link
                esc_html__('A major update of Aggregator is available. %1$s or %2$s to get access to the new and improved aggregator.', 'wprss'),
                '<a href="' . esc_url($migration_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('View version 5.0 details', 'wprss') . '</a>',
                '<a href="' . esc_url($update_url) . '">' . esc_html__('update', 'wprss') . '</a>'
            ) . '
            </span>
        </span>';

        return $html;
    }, 10, 2
);

add_filter(
    'site_transient_update_plugins', function ($updates) {
        if (!wprss_v5_contains_update($updates)) {
            return $updates;
        }

        // Get plugin basename
        $basename = plugin_basename(WPRSS_FILE_CONSTANT);

        // Bail if plugin isn't in update response
        if (empty($updates->response[$basename])) {
            return $updates;
        }

        // Generate update URL with nonce
        $update_url = wp_nonce_url(
            self_admin_url("update.php?action=upgrade-plugin&plugin={$basename}"),
            "upgrade-plugin_{$basename}"
        );

        // Message with HTML
        $msg = sprintf(
            wp_kses(
                __(
                    'This is a major update. Prior testing on a staging site is recommended.<a href="%1$s" target="_blank" rel="noopener noreferrer">View version 5.0 details</a> or <a href="%2$s">update now</a>.',
                    'wprss'
                ),
                [
                'a' => [
                    'href' => [],
                    'target' => [],
                    'rel' => [],
                ],
                ]
            ),
            esc_url('https://www.wprssaggregator.com/help-topics/v5-migration/'),
            esc_url($update_url)
        );

        // Inject into upgrade_notice
        $updates->response[$basename]->upgrade_notice = $msg;

        return $updates;
    }
);

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

function wprss_v5_switch_notice() {
	$dismissed = get_option( 'wprss_v5_switch_dismissed', '0' );
	$dismissed = filter_var( $dismissed, FILTER_VALIDATE_BOOLEAN );
	if ( $dismissed ) {
		return;
	}

	if ( isset( $_GET['page'], $_GET['tab'] )
		&& $_GET['page'] === 'wprss-aggregator-settings'
		&& $_GET['tab'] === 'switch_to_v5'
	) {
		return;
	}

	$has_addons     = wprss_has_active_premium_addons();
	$main_premium   = wprss_is_premium_main_plugin_active();

	if ( $has_addons && ! $main_premium ) {
		echo wprss_v5_notice_render(
			'wprss_v5_switch',
			__( 'Aggregator Free was updated successfully, but your premium features aren’t active.', 'wprss' ),
			sprintf(
				_x(
					'To unlock the full v5 experience, please install the Aggregator Premium plugin before migrating. %s.',
					'%s = "Install Premium" link',
					'wprss'
				),
				sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
					'https://www.wprssaggregator.com/help/installing-aggregator-premium/',
					__( 'Install Premium', 'wprss' )
				)
			)
		);
		return;
	}

	echo wprss_v5_notice_render(
		'wprss_v5_switch',
		__( 'Aggregator was updated successfully, but you’re still using v4.', 'wprss' ),
		sprintf(
			_x(
				'To complete the upgrade and start using Aggregator v5, a migration is required. %s.',
				'%s = "Migrate now" link',
				'wprss'
			),
			sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'edit.php?post_type=wprss_feed&page=wprss-aggregator-settings&tab=switch_to_v5' ),
				__( 'Migrate now', 'wprss' )
			)
		)
	);
}

function wprss_v5_notice_render($id, $title, $content)
{
    $icon = WPRSS_IMG . 'wpra-icon-transparent-new.png';
    $nonce = wp_create_nonce('wpra-dismiss-v5-notice');

    ob_start();
    ?>
    <div id="<?php echo esc_attr($id) ?>" class="notice wpra-v5-notice" data-notice-id="<?php echo esc_attr($id) ?>">
        <input type="hidden" class="wpra-v5-notice-nonce" value="<?php echo esc_attr($nonce) ?>" />

        <div class="wpra-v5-notice-left">
            <img src="<?php echo esc_attr($icon) ?>" style="width: 32px !important" alt="WP RSS Aggregator" />
        </div>
        <div class="wpra-v5-notice-right">
            <h3><?php echo $title ?></h3>
            <p>
                <?php echo $content ?>
            </p>
        </div>
        <button class="wpra-v5-notice-close">
            <span class="dashicons dashicons-no-alt" />
        </button>
    </div>
    <?php

    return ob_get_clean();
}

/**
 * Checks whether any premium add-ons (only add-ons) are installed and active.
 *
 * @return bool
 */
function wprss_has_active_premium_addons() {
	$constants = array(
		'WPRSS_TEMPLATES',
		'WPRSS_C_PATH',
		'WPRSS_ET_PATH',
		'WPRSS_KF_PATH',
		'WPRSS_FTP_PATH',
		'WPRSS_FTR_PATH',
		'WPRSS_WORDAI',
		'WPRSS_SPC_ADDON',
		'WPRA_SC',
	);

	foreach ( $constants as $constant ) {
		if ( defined( $constant ) ) {
			$plugin_file = constant( $constant );

			if ( is_string( $plugin_file ) ) {
				$plugin_basename = plugin_basename( $plugin_file );

				if ( function_exists( 'is_plugin_active' ) && is_plugin_active( $plugin_basename ) ) {
					return true;
				}
			}
		}
	}

	return false;
}

/**
 * Checks if the Aggregator Premium plugin is installed and active.
 *
 * @return bool
 */
function wprss_is_premium_main_plugin_active() {
	$main_premium_plugin = 'wp-rss-aggregator-premium/wp-rss-aggregator-premium.php';

	return function_exists( 'is_plugin_active' ) && is_plugin_active( $main_premium_plugin );
}
