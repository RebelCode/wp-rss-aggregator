<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Util\NullFunction;
use RebelCode\Wpra\Core\Wp\Asset\ScriptAsset;
use RebelCode\Wpra\Core\Wp\Asset\StyleAsset;

/** This module adds a prompt for the 2022 feedback survey. */
class FeedbackSurvey2022Module implements ModuleInterface
{
    const DISMISS_OPTION = 'wpra_2022_survey_dismissed';
    const DISMISS_NONCE = 'wpra_2022_survey_dismiss';
    const DISMISS_ACTION = 'wpra_2022_survey_dismiss';

    /** @inheritdoc */
    public function run(ContainerInterface $c)
    {
        add_action('admin_notices', function () use ($c) {
            if (wprss_is_wprss_page() && !$c->get('survey2022/dismissed')) {
                echo $c->get('survey2022/render_fn')();
            }
        });

        add_action('admin_enqueue_scripts', function () use ($c) {
            if (wprss_is_wprss_page() && !$c->get('survey2022/dismissed')) {
                $c->get('survey2022/js')->enqueue();
                $c->get('survey2022/css')->enqueue();
            }
        });

        add_action('wp_ajax_' . static::DISMISS_ACTION, function () {
            check_admin_referer(static::DISMISS_NONCE);
            update_option(static::DISMISS_OPTION, true);
            echo "Survey Dismissed";
            die;
        });
    }

    /** @inheritdoc */
    public function getFactories()
    {
        return [
            'survey2022/dismissed' => function () {
                return filter_var(get_option(static::DISMISS_OPTION), FILTER_VALIDATE_BOOLEAN);
            },
            'survey2022/template' => function () {
                return 'admin/feedback-survey-2022.twig';
            },
            'survey2022/template/context' => function (ContainerInterface $c) {
                return [
                    'imgPath' => WPRSS_IMG,
                    'isBlackFriday' => $c->get('bf22/is_period'),
                ];
            },
            'survey2022/render_fn' => function (ContainerInterface $c) {
                if (!$c->has('wpra/twig/collection')) {
                    return new NullFunction();
                }

                return function () use ($c) {
                    $collection = $c->get('wpra/twig/collection');
                    $template = $c->get('survey2022/template');

                    return $collection[$template]->render($c->get('survey2022/template/context'));
                };
            },
            'survey2022/js' => function (ContainerInterface $c) {
                $script = new ScriptAsset(
                    'wpra-survey-2022',
                    WPRSS_URI . 'js/survey2022.js',
                    ['jquery'],
                    WPRSS_VERSION,
                    true
                );

                return $script->localize('WpraSurvey2022', function () use ($c) {
                    return $c->get('survey2022/js/l10n');
                });
            },
            'survey2022/js/l10n' => function () {
                return [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce(static::DISMISS_NONCE),
                    'action' => static::DISMISS_ACTION,
                ];
            },
            'survey2022/css' => function () {
                return new StyleAsset('wpra-survey-2022', WPRSS_URI . 'css/survey2022.css', [], WPRSS_VERSION);
            },
        ];
    }

    /** @inheritdoc */
    public function getExtensions()
    {
        return [];
    }
}
