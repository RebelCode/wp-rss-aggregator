<?php

namespace RebelCode\Wpra\Core\Modules;

use DateTime;
use DateTimeZone;
use Exception;
use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Wp\Asset\ScriptAsset;
use RebelCode\Wpra\Core\Wp\Asset\StyleAsset;

/** This module adds the promotion banner for Black Friday 2022. */
class BlackFriday22Module implements ModuleInterface
{
    const DISMISS_OPTION = 'wpra_bf22_dismissed';
    const DISMISS_NONCE = 'wpra_bf22_dismiss';
    const DISMISS_ACTION = 'wpra_bf22_dismiss';

    /** @inheritdoc */
    public function run(ContainerInterface $c)
    {
        add_action('admin_enqueue_scripts', function () use ($c) {
            if (wprss_is_wprss_page() && $c->get('bf22/is_period')) {
                $dismissed = filter_var(get_option(static::DISMISS_OPTION), FILTER_VALIDATE_BOOLEAN);

                if (!$dismissed) {
                    $c->get('bf22/js')->enqueue();
                    $c->get('bf22/css')->enqueue();
                }
            }
        });

        add_action('wp_ajax_' . static::DISMISS_ACTION, function () {
            check_admin_referer(static::DISMISS_NONCE);
            update_option(static::DISMISS_OPTION, true);
            echo "BF22 Dismissed";
            die;
        });
    }

    /** @inheritdoc */
    public function getFactories()
    {
        return [
            'bf22/js' => function (ContainerInterface $c) {
                $script = new ScriptAsset('bf22', WPRSS_URI . 'js/bf22.js', ['jquery'], WPRSS_VERSION, true);

                return $script->localize('BF22', function () use ($c) {
                    return $c->get('bf22/js/l10n');
                });
            },
            'bf22/js/l10n' => function () {
                return [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce(static::DISMISS_NONCE),
                    'action' => static::DISMISS_ACTION,
                ];
            },
            'bf22/css' => function () {
                return new StyleAsset('bf22', WPRSS_URI . 'css/bf22.css', [], WPRSS_VERSION);
            },
            'bf22/is_period' => function () {
                try {
                    $now = new DateTime('now', new DateTimeZone(wprss_get_timezone_string()));
                    $start = new DateTime('2022-11-21T00:00:00', new DateTimeZone('-07:00'));
                    $end = new DateTime('2022-11-29T12:00:00', new DateTimeZone('-07:00'));

                    return $now > $start && $now < $end;
                } catch (Exception $e) {
                    return false;
                }
            },
        ];
    }

    /** @inheritdoc */
    public function getExtensions()
    {
        return [
            'wpra/upsell/plans' => function (ContainerInterface $c, array $plans) {
                if ($c->get('bf22/is_period')) {
                    $plans['pro']['btnLabel'] = $plans['basic']['btnLabel'] = __('Upgrade at 40% off', 'wprss');
                    $plans['pro']['url'] = $plans['basic']['url'] = 'https://www.wprssaggregator.com/pricing/?utm_source=wpra_plugin&utm_medium=wpra_plugin_upgrade&utm_campaign=BF22';
                }

                return $plans;
            },
        ];
    }
}
