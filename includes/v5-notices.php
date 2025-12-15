<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action(
    'admin_notices', function () {
        if (WPRA_V5_USE_V4) {
            wprss_v5_switch_notice();
            wprss_v4_eol_notice();
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

function wprss_v5_notice_render( $id, $title, $content ) {
    $icon  = WPRSS_IMG . 'wpra-icon-transparent-new.png';
    $nonce = wp_create_nonce( 'wpra-dismiss-v5-notice' );

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $post_type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : '';

    ob_start();
    ?>
    <div id="<?php echo esc_attr( $id ); ?>" class="notice wpra-v5-notice" data-notice-id="<?php echo esc_attr( $id ); ?>">
        <input type="hidden" class="wpra-v5-notice-nonce" value="<?php echo esc_attr( $nonce ); ?>" />

        <div class="wpra-v5-notice-left">
            <img src="<?php echo esc_attr( $icon ); ?>" style="width:32px!important" alt="WP RSS Aggregator" />
        </div>

        <div class="wpra-v5-notice-right">
            <h3><?php echo wp_kses_post( $title ); ?></h3>
            <p><?php echo wp_kses_post( $content ); ?></p>
        </div>

        <button class="wpra-v5-notice-close">
            <span class="dashicons dashicons-no-alt"></span>
        </button>
    </div>

    <?php if ( $post_type !== 'wprss_feed' ) : ?>
        <script type="text/javascript">
            (function($){
                $(document).on("click", ".wpra-v5-notice .wpra-v5-notice-close", function () {
                    const $notice = $(this).closest(".wpra-v5-notice");
                    const noticeId = $notice.data("notice-id");
                    const nonce    = $notice.find(".wpra-v5-notice-nonce").val();

                    $.post(ajaxurl, {
                        action: "wprss_dismiss_v5_notice",
                        notice: noticeId,
                        nonce: nonce
                    }).always(function () {
                        $notice.slideUp(150, function () {
                            $notice.remove();
                        });
                    });
                });
            })(jQuery);
        </script>
    <?php endif; ?>

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


function wprss_v4_eol_notice() {
    $deadline_iso = '2026-01-31T23:59:00-08:00';

    ?>
	<div class="notice wpra-v4-eol-notice">
		<div class="wpra-v4-eol-icon">
			<img
				src="<?php echo esc_url( WPRSS_IMG . 'wpra-icon-transparent-new.png' ); ?>"
				alt="WP RSS Aggregator"
			/>
		</div>

		<div class="wpra-v4-eol-content">
			<h3><?php esc_html_e( 'Aggregator v4 Support Ending Soon', 'wprss' ); ?></h3>

			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						__(
							'Support and maintenance for Aggregator v4 has been extended to %s. After this date, the plugin will continue working, but it will no longer be maintained and supported.',
							'wprss'
						),
						'<strong>' . esc_html__( 'January 31, 2026', 'wprss' ) . '</strong>'
					)
				);
				?>
			</p>

			<p>
				<strong>
					<?php esc_html_e( 'We strongly recommend migrating to v5 for continuous support and updates.', 'wprss' ); ?>
				</strong>
			</p>

			<div class="wpra-v4-eol-actions">
				<a
					class="wpra-v4-eol-migrate-btn"
					href="https://www.wprssaggregator.com/help-topics/v5-migration/"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php esc_html_e( 'How to migrate', 'wprss' ); ?>
				</a>

				<a
					class="wpra-v4-eol-contact-link"
					href="https://www.wprssaggregator.com/contact/"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php esc_html_e( 'Contact support', 'wprss' ); ?>
				</a>

				<div
					class="wpra-v4-eol-timer"
					data-deadline="<?php echo esc_attr( $deadline_iso ); ?>"
				>
					<span class="wpra-timer-part wpra-timer-days">
						<strong class="wpra-timer-value">00</strong>
						<span class="wpra-timer-label"><?php esc_html_e( 'Days', 'wprss' ); ?></span>
					</span>

					<span class="wpra-timer-part wpra-timer-hours">
						<strong class="wpra-timer-value">00</strong>
						<span class="wpra-timer-label"><?php esc_html_e( 'Hours', 'wprss' ); ?></span>
					</span>

					<span class="wpra-timer-part wpra-timer-minutes">
						<strong class="wpra-timer-value">00</strong>
						<span class="wpra-timer-label"><?php esc_html_e( 'Minutes', 'wprss' ); ?></span>
					</span>
				</div>

			</div>
		</div>
    </div>
    <?php
}

add_action( 'admin_enqueue_scripts', function () {
    if (!WPRA_V5_USE_V4) {
        return;
    }
    wp_add_inline_script(
        'jquery-core',
        <<<JS
		(function () {
			function updateTimer(container) {
				const deadline = new Date(container.dataset.deadline).getTime();
				const now      = Date.now();
				const diff     = deadline - now;

				let days = 0;
				let hours = 0;
				let minutes = 0;

				if (diff > 0) {
					days    = Math.floor(diff / (1000 * 60 * 60 * 24));
					hours   = Math.floor((diff / (1000 * 60 * 60)) % 24);
					minutes = Math.floor((diff / (1000 * 60)) % 60);
				}

				container.querySelector('.wpra-timer-days .wpra-timer-value').textContent =
					String(days).padStart(2, '0');

				container.querySelector('.wpra-timer-hours .wpra-timer-value').textContent =
					String(hours).padStart(2, '0');

				container.querySelector('.wpra-timer-minutes .wpra-timer-value').textContent =
					String(minutes).padStart(2, '0');
			}

			document.addEventListener('DOMContentLoaded', function () {
				document.querySelectorAll('.wpra-v4-eol-timer').forEach(function (container) {
					updateTimer(container);
					setInterval(function () {
						updateTimer(container);
					}, 60000);
				});
			});
		})();
		JS
    );
} );

add_action( 'admin_enqueue_scripts', function () {
    if (!WPRA_V5_USE_V4) {
        return;
    }
    wp_add_inline_style(
        'wp-admin',
        <<<CSS
	.wpra-v4-eol-notice {
		border-left: 4px solid #E5533D;
		background: #fff;
		display: flex;
		gap: 16px;
		padding-left: 0 !important;
		margin-left: 0 !important;
	}

	.wpra-v4-eol-icon img {
		width: 32px;
		height: 32px;
	}

	.wpra-v4-eol-icon {
		background: var(--WPRA-light-orange, #FDF3E9);
		padding: 20px 10px;
	}

	.wpra-v4-eol-content {
		padding-right: 20px;
		padding-top: 20px;
		padding-bottom: 20px;
	}

	.wpra-v4-eol-content h3 {
		margin: 0 0 8px;
	}

	.wpra-v4-eol-actions {
		display: flex;
		align-items: center;
		gap: 14px;
		margin-top: 12px;
	}

	.wpra-v4-eol-timer {
		display: flex;
		gap: 12px;
		background: #EFEFEF;
		padding: 10px;
		border-radius: 3px;
	}

	.wpra-timer-part {
		display: flex;
		align-items: baseline;
		gap: 4px;
	}

	.wpra-timer-value {
		font-size: 20px;
	}

	.wpra-timer-label {
		font-size: 14px;
	}

	.wpra-v4-eol-contact-link {
		background: none !important;
		border: 0 !important;
		box-shadow: none !important;
		padding: 0;
		margin: 0;
		color: #E5533D;
		text-decoration: none;
	}

	.wpra-v4-eol-contact-link:hover,
	.wpra-v4-eol-contact-link:focus {
		color: #c94432;
		text-decoration: underline;
	}

	.wpra-v4-eol-migrate-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		padding: 8px 14px;
		border-radius: 3px;
		background-color: #E5533D;
		color: #ffffff !important;
		text-decoration: none;
		border: none;
		box-shadow: none;
	}

	.wpra-v4-eol-migrate-btn:hover,
	.wpra-v4-eol-migrate-btn:focus {
		background-color: #c94432;
		color: #ffffff;
		text-decoration: none;
	}
CSS
    );
} );
