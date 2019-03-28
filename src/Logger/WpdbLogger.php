<?php

namespace RebelCode\Wpra\Core\Logger;

use Psr\Log\AbstractLogger;
use RebelCode\Wpra\Core\Database\TableInterface;

/**
 * A PSR-3 logger that saves logs in the WordPress database.
 *
 * @since [*next-version*]
 */
class WpdbLogger extends AbstractLogger implements ClearableLoggerInterface, LogReaderInterface
{
    /* @since [*next-version*] */
    use LoggerUtilsTrait;

    /**
     * The log's ID property and default column name.
     *
     * @since [*next-version*]
     */
    const LOG_ID = 'id';

    /**
     * The log's date property and default column name.
     *
     * @since [*next-version*]
     */
    const LOG_DATE = 'date';

    /**
     * The log's level property and default column name.
     *
     * @since [*next-version*]
     */
    const LOG_LEVEL = 'level';

    /**
     * The log's message property and default column name.
     *
     * @since [*next-version*]
     */
    const LOG_MESSAGE = 'message';

    /**
     * The table where logs are stored.
     *
     * @since [*next-version*]
     *
     * @var TableInterface
     */
    protected $table;

    /**
     * A mapping of log properties to table column names.
     *
     * @see   COL_DATE
     * @see   COL_LEVEL
     * @see   COL_MESSAGE
     *
     * @since [*next-version*]
     *
     * @var string[]
     */
    protected $columns;

    /**
     * Optional mapping of additional columns and fixed data to insert into a row.
     *
     * @since [*next-version*]
     *
     * @var string[]
     */
    protected $extra;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TableInterface $table   The table where logs are stored.
     * @param array          $columns A mapping of log data keys to column names. The `COL_*` constants may be used to
     *                                map standard log properties and any non-standard properties may also be included
     *                                to insert fixed values with the $extra parameter.
     * @param array          $extra   A mapping of log properties and the values to insert for them. Beware that the
     *                                standard log properties MAY be overridden if specified as keys for this
     *                                parameter. All non-standard data is stored in the table as VARCHAR with a limit
     *                                of 100 characters.
     */
    public function __construct(TableInterface $table, $columns = [], $extra = [])
    {
        $this->table = $table;
        $this->columns = array_merge($this->getDefaultColumns(), $columns);
        $this->extra = $extra;

        // Make sure the table exists
        $this->table->create();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function log($level, $message, array $context = [])
    {
        $fullMsg = $this->interpolate($message, $context);
        $rowData = $this->getLogRowData($level, $fullMsg);

        $this->table[] = $rowData;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function clearLogs()
    {
        $this->table->clear();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getLogs($num = null, $page = 1)
    {
        $table = $this->table;

        if ($num !== null) {
            // Ensure num and page are at least 1
            $num = max(1, $num);
            $page = max(1, $page);
            // Calculate the offset
            $offset = $num * ($page - 1);

            $table = $table->filter([
                TableInterface::FILTER_LIMIT => $num,
                TableInterface::FILTER_OFFSET => $offset,
            ]);
        }

        $table = $table->filter([
            TableInterface::FILTER_ORDER_BY => $this->columns[static::LOG_DATE],
            TableInterface::FILTER_ORDER => 'ASC',
        ]);

        $logs = [];
        foreach ($table as $row) {
            $log = [];
            foreach ($this->columns as $prop => $col) {
                $log[$prop] = $row[$col];
            }
            $logs[] = $log;
        }

        return $logs;
    }

    /**
     * Retrieves the data to insert for a single log's row.
     *
     * @since [*next-version*]
     *
     * @param string $level   The log's level.
     * @param string $message The log's message.
     *
     * @return array An associative array containing the columns as keys mapping to their respective values.
     */
    protected function getLogRowData($level, $message)
    {
        $data = [];

        // Iterate the columns and retrieve the data to insert for each
        foreach ($this->columns as $prop => $col) {
            $data[$col] = $this->getLogPropData($prop, $level, $message);
        }

        return $data;
    }

    /**
     * Retrieves the data for a single log's property.
     *
     * @since [*next-version*]
     *
     * @param string $prop    The name of the property for which to return data.
     * @param string $level   The log's level.
     * @param string $message The log's message.
     *
     * @return mixed The data to insert for the specified log property.
     */
    protected function getLogPropData($prop, $level, $message)
    {
        // Ignore the ID and date columns - they are auto populated by the DB
        if ($prop === static::LOG_ID || $prop === static::LOG_DATE) {
            return null;
        }

        if ($prop === static::LOG_LEVEL) {
            return $level;
        }

        if ($prop === static::LOG_MESSAGE) {
            return $message;
        }

        return isset($this->extra[$prop])
            ? $this->extra[$prop]
            : null;
    }

    /**
     * Retrieves the default columns.
     *
     * @since [*next-version*]
     *
     * @return array A map of log property keys mapping to their respective column names.
     */
    protected function getDefaultColumns()
    {
        return [
            static::LOG_ID => static::LOG_ID,
            static::LOG_DATE => static::LOG_DATE,
            static::LOG_LEVEL => static::LOG_LEVEL,
            static::LOG_MESSAGE => static::LOG_MESSAGE,
        ];
    }
}
