<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\Logger;

use RebelCode\Wpra\Core\Logger\LogReaderInterface;

/**
 * Handles log download requests.
 *
 * @since [*next-version*]
 */
class DownloadLogHandler
{
    /**
     * @var LogReaderInterface
     */
    protected $reader;

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
     * @param LogReaderInterface $reader    The log reader.
     * @param string             $nonceName The name of the nonce to verify requests.
     */
    public function __construct(LogReaderInterface $reader, $nonceName)
    {
        $this->reader = $reader;
        $this->nonceName = $nonceName;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $downloadLog = filter_input(INPUT_POST, 'wpra-download-log', FILTER_DEFAULT);

        if (empty($downloadLog) || !check_admin_referer($this->nonceName)) {
            return;
        }

        wprss_download_log();
    }
}
