<?php

namespace RebelCode\Wpra\Core\Modules;

use DateTime;
use DateTimeZone;
use Exception;
use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Wp\Asset\ScriptAsset;
use RebelCode\Wpra\Core\Wp\Asset\StyleAsset;

/** This module adds the promotion banner for Black Friday 2023. */
class BlackFriday23Module implements ModuleInterface
{
    const DISMISS_OPTION = 'wpra_bf23_dismissed';
    const DISMISS_NONCE = 'wpra_bf23_dismiss';
    const DISMISS_ACTION = 'wpra_bf23_dismiss';
    const FREE_URL = 'https://www.wprssaggregator.com/upgrade/?utm_source=wpra_plugin&utm_medium=banner&utm_campaign=BF23';
    const PREMIUM_URL = 'https://www.wprssaggregator.com/account/orders/?discount=BF30&utm_source=wpra_plugin&utm_medium=banner&utm_campaign=BF23';
    const END_DATE = '2023-11-28T00:00:00.000-07:00';

    /** @inheritdoc */
    public function run(ContainerInterface $c)
    {
        add_action('admin_enqueue_scripts', function () use ($c) {
            if (wprss_is_wprss_page() && $c->get('bf23/is_period')) {
                $dismissed = filter_var(get_option(static::DISMISS_OPTION), FILTER_VALIDATE_BOOLEAN);

                if (!$dismissed) {
                    $c->get('bf23/js')->enqueue();
                    $c->get('bf23/css')->enqueue();
                }
            }
        });

        add_action('wp_ajax_' . static::DISMISS_ACTION, function () {
            check_admin_referer(static::DISMISS_NONCE);
            update_option(static::DISMISS_OPTION, true);
            echo "BF23 Dismissed";
            die;
        });
    }

    /** @inheritdoc */
    public function getFactories()
    {
        return [
            'bf23/js' => function (ContainerInterface $c) {
                $script = new ScriptAsset('bf23', WPRSS_URI . 'js/bf23.js', ['jquery'], WPRSS_VERSION, true);

                return $script->localize('BF23', function () use ($c) {
                    return $c->get('bf23/js/l10n');
                });
            },
            'bf23/js/l10n' => function (ContainerInterface $c) {
                return [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce(static::DISMISS_NONCE),
                    'action' => static::DISMISS_ACTION,
                    'url' => $c->get('bf23/url'),
                    'endDate' => static::END_DATE,
                    'discount' => '30%',
                    'coupon' => 'BF30',
                ];
            },
            'bf23/css' => function () {
                return new StyleAsset('bf23', WPRSS_URI . 'css/bf23.css', [], WPRSS_VERSION);
            },
            'bf23/is_period' => function () {
                $getParam = filter_input(INPUT_GET, 'bf23', FILTER_VALIDATE_BOOLEAN);
                if ($getParam) {
                    return true;
                }

                try {
                    $now = new DateTime('now', new DateTimeZone(wprss_get_timezone_string()));
                    $start = new DateTime('2023-11-20T00:00:00', new DateTimeZone('-07:00'));
                    $end = new DateTime('2023-11-28T12:00:00', new DateTimeZone('-07:00'));

                    return $now > $start && $now < $end;
                } catch (Exception $e) {
                    return false;
                }
            },
            'bf23/url' => function () {
                if (wprss_get_addons() === 0) {
                    return self::FREE_URL;
                } else {
                    return self::PREMIUM_URL;
                }
            },
        ];
    }

    /** @inheritdoc */
    public function getExtensions()
    {
        return [
            'wpra/upsell/plans' => function (ContainerInterface $c, array $plans) {
                if ($c->get('bf23/is_period')) {
                    $plans['pro']['btnLabel'] = $plans['basic']['btnLabel'] = __('Upgrade at 30% off', 'wprss');
                    $plans['pro']['url'] = $plans['basic']['url'] = 'https://www.wprssaggregator.com/pricing/?utm_source=wpra_plugin&utm_medium=wpra_plugin_upgrade&utm_campaign=BF23';
                }

                return $plans;
            },
        ];
    }
}
