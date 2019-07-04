<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Database\NullTable;
use RebelCode\Wpra\Core\Database\WpdbTable;
use RebelCode\Wpra\Core\Handlers\Logger\ClearLogHandler;
use RebelCode\Wpra\Core\Handlers\Logger\DownloadLogHandler;
use RebelCode\Wpra\Core\Handlers\Logger\RenderLogHandler;
use RebelCode\Wpra\Core\Handlers\Logger\SaveLogOptionsHandler;
use RebelCode\Wpra\Core\Handlers\Logger\TruncateLogsCronHandler;
use RebelCode\Wpra\Core\Handlers\ScheduleCronJobHandler;
use RebelCode\Wpra\Core\Logger\ConditionalLogger;
use RebelCode\Wpra\Core\Logger\FeedLoggerDataSet;
use RebelCode\Wpra\Core\Logger\WpdbLogger;

/**
 * A module that adds a logger to WP RSS Aggregator.
 *
 * @since 4.13
 */
class LoggerModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function getFactories()
    {
        return [
            /*
             * Whether or not logging is enabled.
             *
             * @since 4.14
             */
            'wpra/logging/enabled' => function (ContainerInterface $c) {
                return $c->get('wpra/core/config')['logging/enabled'];
            },
            /*
             * The main logger instance.
             *
             * @since 4.13
             */
            'wpra/logging/logger' => function (ContainerInterface $c) {
                return new ConditionalLogger(
                    $c->get('wpra/logging/wpdb_logger'),
                    $c->get('wpra/logging/enabled')
                );
            },
            /*
             * The WPDB logger instance.
             *
             * @since 4.13
             */
            'wpra/logging/wpdb_logger' => function (ContainerInterface $c) {
                return new WpdbLogger(
                    $c->get('wpra/logging/log_table'),
                    $c->get('wpra/logging/log_table_columns'),
                    $c->get('wpra/logging/log_table_extra')
                );
            },
            /*
             * The log reader instance.
             *
             * @since 4.14
             */
            'wpra/logging/reader' => function (ContainerInterface $c) {
                return $c->get('wpra/logging/wpdb_logger');
            },
            /*
             * The log clearer instance.
             *
             * @since 4.14
             */
            'wpra/logging/clearer' => function (ContainerInterface $c) {
                return $c->get('wpra/logging/wpdb_logger');
            },
            /*
             * The table where logs are stored.
             *
             * Resolves to a null table if WordPress' database adapter is not available.
             *
             * @since 4.13
             */
            'wpra/logging/log_table' => function (ContainerInterface $c) {
                if (!$c->has('wp/db')) {
                    return new NullTable();
                }

                return new WpdbTable(
                    $c->get('wp/db'),
                    $c->get('wpra/logging/log_table_name'),
                    $c->get('wpra/logging/log_table_schema'),
                    $c->get('wpra/logging/log_table_primary_key')
                );
            },
            /*
             * The name of the table where logs are stored.
             *
             * @since 4.13
             */
            'wpra/logging/log_table_name' => function (ContainerInterface $c) {
                return 'wprss_logs';
            },
            /*
             * The table columns to use for the log table.
             *
             * @since 4.13
             */
            'wpra/logging/log_table_schema' => function () {
                return [
                    'id' => 'BIGINT NOT NULL AUTO_INCREMENT',
                    'date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                    'level' => 'varchar(30) NOT NULL',
                    'message' => 'text NOT NULL',
                    'feed_id' => 'varchar(100)',
                ];
            },
            /*
             * The mapping of log properties to columns.
             *
             * @since 4.13
             */
            'wpra/logging/log_table_columns' => function () {
                return [
                    WpdbLogger::LOG_ID => 'id',
                    WpdbLogger::LOG_DATE => 'date',
                    WpdbLogger::LOG_LEVEL => 'level',
                    WpdbLogger::LOG_MESSAGE => 'message',
                    'feed_id' => 'feed_id',
                ];
            },
            /*
             * The log table's primary key.
             *
             * @since 4.13
             */
            'wpra/logging/log_table_primary_key' => function () {
                return 'id';
            },
            /*
             * Additional data to include per-log in the log table.
             *
             * @since 4.13
             */
            'wpra/logging/log_table_extra' => function () {
                return [
                    'feed_id' => '',
                ];
            },
            /*
             * The data set that contains the logger instances for each feed source.
             *
             * @since 4.13
             */
            'wpra/logging/feed_logger_dataset' => function (ContainerInterface $c) {
                return new FeedLoggerDataSet($c->get('wpra/logging/feed_logger_factory'));
            },
            /*
             * The factory that creates logger instances for specific feeds.
             *
             * @since 4.13
             */
            'wpra/logging/feed_logger_factory' => function (ContainerInterface $c) {
                return function ($feedId) use ($c) {
                    return new ConditionalLogger(
                        new WpdbLogger(
                            $c->get('wpra/logging/log_table'),
                            $c->get('wpra/logging/log_table_columns'),
                            ['feed_id' => $feedId]
                        ),
                        $c->get('wpra/logging/enabled')
                    );
                };
            },
            /*
             * The scheduler for the log truncation cron job.
             *
             * @since 4.13
             */
            'wpra/logging/trunc_logs_cron/scheduler' => function (ContainerInterface $c) {
                return new ScheduleCronJobHandler(
                    $c->get('wpra/logging/trunc_logs_cron/event'),
                    $c->get('wpra/logging/trunc_logs_cron/handler'),
                    $c->get('wpra/logging/trunc_logs_cron/first_run'),
                    $c->get('wpra/logging/trunc_logs_cron/frequency'),
                    $c->get('wpra/logging/trunc_logs_cron/args')
                );
            },
            /*
             * The event for the log truncation cron job.
             *
             * @since 4.13'
             */
            'wpra/logging/trunc_logs_cron/event' => function (ContainerInterface $c) {
                return 'wprss_truncate_logs';
            },
            /*
             * How frequently the log truncation cron job runs.
             *
             * @since 4.13'
             */
            'wpra/logging/trunc_logs_cron/frequency' => function (ContainerInterface $c) {
                return 'daily';
            },
            /*
             * When to first run the log truncation cron job after it's been scheduled.
             *
             * @since 4.13'
             */
            'wpra/logging/trunc_logs_cron/first_run' => function (ContainerInterface $c) {
                return time() + DAY_IN_SECONDS;
            },
            /**
             * The URL of the page where the log is found.
             *
             * @since 4.14
             */
            'wpra/logging/page/url' => function () {
                return admin_url('edit.php?post_type=wprss_feed&page=wprss-debugging');
            },
            /**
             * The name of the nonce used in the debug page to verify the referer of log-related requests.
             *
             * @since 4.14
             */
            'wpra/logging/page/nonce_name' => function () {
                return 'wprss-debug-log';
            },
            /**
             * The handler that renders the log.
             *
             * @since 4.14
             */
            'wpra/logging/handlers/render_log' => function (ContainerInterface $c) {
                return new RenderLogHandler(
                    $c->get('wpra/logging/reader'),
                    $c->get('wpra/twig/collection')['admin/debug/log.twig'],
                    $c->get('wpra/core/config'),
                    $c->get('wpra/logging/page/nonce_name')
                );
            },
            /**
             * TThe handler that processes the clear log request.
             *
             * @since 4.14
             */
            'wpra/logging/handlers/clear_log' => function (ContainerInterface $c) {
                return new ClearLogHandler(
                    $c->get('wpra/logging/clearer'),
                    $c->get('wpra/logging/page/nonce_name')
                );
            },
            /**
             * The handler that processes the log download request.
             *
             * @since 4.14
             */
            'wpra/logging/handlers/download_log' => function (ContainerInterface $c) {
                return new DownloadLogHandler(
                    $c->get('wpra/logging/reader'),
                    $c->get('wpra/logging/page/nonce_name')
                );
            },
            /**
             * The handler that processes log option saving request.
             *
             * @since 4.14
             */
            'wpra/logging/handlers/save_options' => function (ContainerInterface $c) {
                return new SaveLogOptionsHandler(
                    $c->get('wpra/core/config'),
                    $c->get('wpra/logging/page/nonce_name')
                );
            },
            /*
             * The handler for the log truncation cron job.
             *
             * @since 4.13'
             */
            'wpra/logging/trunc_logs_cron/handler' => function (ContainerInterface $c) {
                return new TruncateLogsCronHandler(
                    $c->get('wpra/logging/log_table'),
                    $c->get('wpra/core/config')
                );
            },
            /*
             * The arguments to pass to the log truncation cron job handler.
             *
             * @since 4.13'
             */
            'wpra/logging/trunc_logs_cron/args' => function (ContainerInterface $c) {
                return [];
            },
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function getExtensions()
    {
        return [
            /*
             * Adds the logging options to the plugin config.
             *
             * @since 4.14
             */
            'wpra/core/config/options' => function (ContainerInterface $c, $options) {
                $options['logging/enabled'] = true;
                $options['logging/limit_days'] = 3;

                return $options;
            },
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function run(ContainerInterface $c)
    {
        // Hook in the scheduler for the truncate logs cron job
        add_action('init', $c->get('wpra/logging/trunc_logs_cron/scheduler'));

        // Renders the log on the debugging page
        add_filter('wprss_debug_operations', function ($operations) use ($c) {
            $operations['render-error-log'] = apply_filters(
                'wprss_render_error_log_operation',
                [
                    'nonce' => null,
                    'run' => null,
                    'render' => $c->get('wpra/logging/handlers/render_log'),
                ]
            );

            return $operations;
        });

        // Register the clear log handler
        add_action('admin_init', $c->get('wpra/logging/handlers/clear_log'));
        // Register the download log handler
        add_action('admin_init', $c->get('wpra/logging/handlers/download_log'));
        // Register the save log options handler
        add_action('admin_init', $c->get('wpra/logging/handlers/save_options'));
    }
}
