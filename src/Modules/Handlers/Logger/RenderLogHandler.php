<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\Logger;

use Dhii\Output\TemplateInterface;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Logger\LogReaderInterface;

/**
 * The handler that renders the log.
 *
 * @since [*next-version*]
 */
class RenderLogHandler
{
    /**
     * @since [*next-version*]
     *
     * @var LogReaderInterface
     */
    protected $reader;

    /**
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $template;

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
     * @since [*next-version*]
     *
     * @var int
     */
    protected $numLogs;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param LogReaderInterface $reader    The log reader.
     * @param TemplateInterface  $template  The template to render.
     * @param DataSetInterface   $config    The logging config.
     * @param string             $nonceName The name of the nonce to use for log operations.
     * @param int                $numLogs   The number of logs to show.
     */
    public function __construct(
        LogReaderInterface $reader,
        TemplateInterface $template,
        DataSetInterface $config,
        $nonceName,
        $numLogs = 200
    ) {
        $this->reader = $reader;
        $this->template = $template;
        $this->config = $config;
        $this->numLogs = $numLogs;
        $this->nonceName = $nonceName;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        echo $this->template->render([
            'logs'               => $this->reader->getLogs($this->numLogs),
            'nonce_name'         => $this->nonceName,
            'config'             => $this->config,
            'show_options'       => true,
        ]);
    }
}
