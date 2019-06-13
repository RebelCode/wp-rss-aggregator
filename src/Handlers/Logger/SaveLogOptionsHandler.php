<?php

namespace RebelCode\Wpra\Core\Handlers\Logger;

use RebelCode\Wpra\Core\Data\DataSetInterface;

/**
 * Handles log option save requests.
 *
 * @since [*next-version*]
 */
class SaveLogOptionsHandler
{
    /**
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $config;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $nonceName;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface $config    The logging config data set.
     * @param string           $nonceName The name of the nonce to verify requests.
     */
    public function __construct(DataSetInterface $config, $nonceName)
    {
        $this->config = $config;
        $this->nonceName = $nonceName;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $logOptions = filter_input(INPUT_POST, 'wpra-log-options', FILTER_DEFAULT);

        if (empty($logOptions) || !check_admin_referer($this->nonceName)) {
            return;
        }

        $this->config['logging/enabled'] = filter_input(
            INPUT_POST,
            'logging_enabled',
            FILTER_VALIDATE_BOOLEAN
        );

        $this->config['logging/limit_days'] = filter_input(
            INPUT_POST,
            'logging_limit_days',
            FILTER_VALIDATE_INT
        );
    }
}
