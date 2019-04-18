<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\Logger;

use RebelCode\Wpra\Core\Database\TableInterface;

/**
 * The handler for the log truncation cron job.
 *
 * @since [*next-version*]
 */
class TruncateLogsCronHandler
{
    /**
     * The log table to truncate.
     *
     * @since [*next-version*]
     *
     * @var TableInterface
     */
    protected $table;

    /**
     * Logs older than this number of days will be deleted.
     *
     * @since [*next-version*]
     *
     * @var int
     */
    protected $days;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TableInterface $table The log table.
     * @param int            $days  Logs older than this number of days will be deleted.
     */
    public function __construct(TableInterface $table, $days)
    {
        $this->table = $table;
        $this->days = $days;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        // Filter to retrieve logs older than 60 days
        $table = $this->table->filter([
            'where' => 'DATEDIFF(CURDATE(), `date`) > 30',
        ]);

        // Clear the filtered table
        $table->clear();
    }
}
