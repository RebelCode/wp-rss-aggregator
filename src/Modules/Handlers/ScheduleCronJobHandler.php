<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

/**
 * A generic handler that schedules a WordPress cron job and its handler.
 *
 * @since [*next-version*]
 */
class ScheduleCronJobHandler
{
    /**
     * The action to invoke when the cron job is run.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $key;

    /**
     * The cron job handler.
     *
     * @since [*next-version*]
     *
     * @var callable
     */
    protected $handler;

    /**
     * The timestamp for when to run the cron job, or null to run immediately.
     *
     * @since [*next-version*]
     *
     * @var int|null
     */
    protected $firstRun;

    /**
     * How frequently to run the cron job, or null to run only once.
     *
     * @since [*next-version*]
     *
     * @var string|null
     */
    protected $frequency;

    /**
     * The arguments to pass to the cron handler.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $args;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string      $key       The key to use to identify this cron job.
     * @param callable    $handler   The cron job handler to invoke.
     * @param int|null    $firstRun  The timestamp for when to run the cron job, or null to run immediately.
     * @param string|null $frequency How frequently to run the cron job, or null to run only once.
     * @param array       $args      The arguments to pass to the cron handler.
     */
    public function __construct($key, callable $handler, $firstRun = null, $frequency = null, $args = [])
    {
        $this->key = $key;
        $this->handler = $handler;
        $this->firstRun = $firstRun;
        $this->frequency = $frequency;
        $this->args = $args;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        // Attach the handler to the action
        add_action($this->key, $this->handler);

        if (wp_next_scheduled($this->key) !== false) {
            return false;
        }

        $start = ($this->firstRun === null) ? time() : intval($this->firstRun);

        if ($this->frequency === null) {
            return wp_schedule_single_event($start, $this->key, $this->args);
        }

        return wp_schedule_event($start, $this->frequency, $this->key, $this->args);
    }
}
