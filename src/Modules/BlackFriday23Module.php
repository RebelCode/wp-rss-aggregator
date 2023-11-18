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
    public const DISMISS_OPTION = 'wpra_bf23_dismissed';
    public const DISMISS_NONCE = 'wpra_bf23_dismiss';
    public const DISMISS_ACTION = 'wpra_bf23_dismiss';
    public const FREE_URL = 'https://www.wprssaggregator.com/upgrade/?utm_source=wpra_plugin&utm_medium=banner&utm_campaign=BF23';
    public const PREMIUM_URL = 'https://www.wprssaggregator.com/account/orders/?discount=BF30&utm_source=wpra_plugin&utm_medium=banner&utm_campaign=BF23';
    public const END_DATE = '2023-11-28T00:00:00.000-08:00';

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

        add_filter('wprss_admin_footer_links', function ($links) use ($c) {
            if ($c->get('bf23/is_period')) {
                $links['upgrade']['heading'] = 'Upgrade at 30% off';
                $links['upgrade']['text'] = 'BLACK FRIDAY OFFER';
                $links['upgrade']['url'] = count(wprss_get_addons()) > 0
                   ? self::PREMIUM_URL
                   : self::FREE_URL;
            }

            return $links;
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
                if (count(wprss_get_addons()) > 0) {
                    return self::PREMIUM_URL;
                } else {
                    return self::FREE_URL;
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
                    foreach ($plans as $i => $plan) {
                        $plans[$i]['btnLabel'] = __('Upgrade at 30% off', 'wprss');
                        $plans[$i]['url'] = 'https://www.wprssaggregator.com/upgrade/?utm_source=wpra_plugin&utm_medium=features&utm_campaign=BF23';
                        $plans[$i]['highlight'] = true;
                    }
                }

                return $plans;
            },
            'wpra/upsell/more_features_page/args' => function (ContainerInterface $c, array $args) {
                if ($c->get('bf23/is_period')) {
                    $args['spotlightUrl'] = 'https://spotlightwp.com/pricing/?utm_source=wpra_plugin&utm_medium=features&utm_campaign=BF23';
                    $args['spotlightBtnText'] = __('Try Spotlight at 30% off', 'wprss');
                }

                return $args;
            },
        ];
    }
}
