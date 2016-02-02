<?php

namespace Aventura\Wprss\Core\Component;

use Aventura\Wprss\Core;

/**
 * A WPRSS-specific implementation, ready to use.
 *
 * @since 4.8.1
 */
class Logger extends Core\Model\LoggerAbstract
{
    const WPRA_LEVEL_PREFIX = 'WPRSS_LOG_LEVEL_';

    /**
     * {@inheritdoc}
     *
     * Adds an aditional 'SYSTEM' > 'DEBUG' level conversion.
     *
     * @since 4.8.1
     * @param string $level
     * @return int
     */
    public static function toMonologLevel($level)
    {
        // For compatibility with WPRA
        if (strtoupper($level) === 'SYSTEM') {
            $level = 'DEBUG';
        }
        return parent::toMonologLevel($level);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.8.1
     */
    public function shouldAddRecord($level, $message, array $context = array())
    {
        return $this->shouldLogLevel($level);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.8.1
     * @see getLevelThreshold()
     * @param int|string $level A Monolog level to check.
     * @return boolean True if the level should be logged; false otherwise.
     */
    public function shouldLogLevel($level)
    {
        $level = static::monologToWpraLevel($level);
        $threshold = $this->getLevelThreshold();
        if (is_null($threshold)) {
            return true;
        }

        $threshold = intval($threshold);
        if ($threshold === 0) {
            return false;
        }

        $thisLevelOnly = $threshold > 0;
        $threshold = abs($threshold);

        return $thisLevelOnly
            ? $level === $threshold
            : $level <= $threshold;
    }

    /**
     * Converts a WPRA level to a Monolog one.
     *
     * @since 4.8.1
     * @param int"string $level A numeric or string representation of a WPRA level.
     *  If is WPRA-prefixed, i.e. a full constant name, the prefix will be removed.
     * @return int The numeric representation of the corresponding Monolog level.
     * @throws \InvalidArgumentException If no such Monolog level defined.
     */
    public static function wpraToMonologLevel($level)
    {
        $wpraPrefix = static::WPRA_LEVEL_PREFIX;
        if (!is_numeric($level) && stripos($level, $wpraPrefix)) {
            $level = substr($level, strlen($wpraPrefix));
        }

        if (is_string($level) || $level > 50) {
            return static::toMonologLevel($level);
        }

        $levels = wprss_log_get_levels();
        if (!isset($levels[$level])) {
            throw new \InvalidArgumentException(sprintf('Monolog Level "%1$s" is not defined', $level));
        }

        $level = $levels[$level];
        return static::toMonologLevel($level);
    }

    /**
     * Converts a Monolog level to a WPRA one.
     *
     * @since 4.8.1
     * @param string|int $level A Monolog level's string or numeric representation.
     * @return int The WPRA level's numeric representation that corresponds to the specified Monolog one.
     * @throws \InvalidArgumentException If no such WPRA level defined.
     */
    public static function monologToWpraLevel($level)
    {
        if (is_numeric($level)) {
            $level = static::getLevelName($level);
        }
        if ($level === 'DEBUG') {
            $level = 'SYSTEM';
        }

        $constName = static::WPRA_LEVEL_PREFIX . $level;
        if (!defined($constName)) {
            throw new \InvalidArgumentException(sprintf('WPRA Level "%1$s" is not defined', $level));
        }

        return constant($constName);
    }
}
