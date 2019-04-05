<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Database\WpdbTable;
use RebelCode\Wpra\Core\Logger\WpdbLogger;

/**
 * A module that adds a logger to WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class LoggerModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            /*
             * The main logger instance.
             *
             * @since [*next-version*]
             */
            'wpra/logs/logger' => function (ContainerInterface $c) {
                return new WpdbLogger(
                    $c->get('wpra/logs/log_table'),
                    $c->get('wpra/logs/log_table_columns'),
                    $c->get('wpra/logs/log_table_extra')
                );
            },
            /*
             * The table where logs are stored.
             *
             * Resolves to a null table if WordPress' database adapter is not available.
             *
             * @since [*next-version*]
             */
            'wpra/logs/log_table' => function (ContainerInterface $c) {
                if (!$c->has('wp/db')) {
                    return new NullTable();
                }
                return new WpdbTable(
                    $c->get('wp/db'),
                    $c->get('wpra/logs/log_table_name'),
                    $c->get('wpra/logs/log_table_schema'),
                    $c->get('wpra/logs/log_table_primary_key')
                );
            },
            /*
             * The name of the table where logs are stored.
             *
             * @since [*next-version*]
             */
            'wpra/logs/log_table_name' => function (ContainerInterface $c) {
                return 'wprss_logs';
            },
            /*
             * The table columns to use for the log table.
             *
             * @since [*next-version*]
             */
            'wpra/logs/log_table_schema' => function () {
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
             * @since [*next-version*]
             */
            'wpra/logs/log_table_columns' => function () {
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
             * @since [*next-version*]
             */
            'wpra/logs/log_table_primary_key' => function () {
                return 'id';
            },
            /*
             * Additional data to include per-log in the log table.
             *
             * @since [*next-version*]
             */
            'wpra/logs/log_table_extra' => function () {
                return [
                    'feed_id' => '',
                ];
            },
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
    }
}
