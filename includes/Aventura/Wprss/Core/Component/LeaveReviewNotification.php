<?php

namespace Aventura\Wprss\Core\Component;

use Aventura\Wprss\Core;

/**
 * The component responsible for the "Leave a Review" notification.
 *
 * On plugin activation, records that time, if not already recorded.
 * When retrieved, looks for that time. If not recorded, deduces it from the
 * creation time of the first feed source. If no feed sources, assumes current
 * time at the moment of retrieval. This result is recorded as the first
 * activation time, and is used in subsequent requests.
 *
 * @since [*next-version*]
 */
class LeaveReviewNotification extends Core\Plugin\ComponentAbstract
{
    const FIRST_ACTIVATION_TIME_OPTION_SUFFIX = '_first_activation_time';

    protected $firstActivationTime;
    protected $firstActivationTimeOptionName;

    /**
     * Runs when this component is initialized.
     *
     * @since [*next-version*]
     */
    public function hook()
    {
        $hookName = sprintf('activate_%1$s', $this->getPlugin()->getBasename());
        $this->on($hookName, 'onPluginActivated');
    }

    /**
     * Runs when the parent plugin is activated.
     *
     * @since [*next-version*]
     */
    public function onPluginActivated()
    {
        $this->recordCurrentTime();
    }

    /**
     * Retrieve the time of the first activation of the plugin.
     *
     * @since [*next-version*]
     *
     * @return int|string The first activation time in GMT as Unix timestamp.
     *  If `$format` is not `null`, formats the timestamp using the specified format string.
     */
    public function getFirstActivationTime($format = null)
    {
        $time = $this->_getFirstActivationTimeDb();
        if (is_null($time)) {
            $time = $this->_calculateFirstActivationTime();
            $this->_setFirstActivationTimeDb($time);
        }

        return is_null($format)
                ? $time
                : date($format, $time);
    }

    /**
     * Deduces what the first activation time is, if it is not recorded.
     *
     * @since [*next-version*]
     *
     * @return int The first activation time, calculated, in the GMT zone.
     */
    protected function _calculateFirstActivationTime()
    {
        $feedSources = $this->_getFeedSources(1);
        if (!count($feedSources)) {
            return $this->_getCurrentTimestampGmt();
        }

        $firstFeedSource = $feedSources[0];
        /* @var $firstFeedSource \WP_Post */
        $firstFeedSourceCreated = $firstFeedSource->post_date_gmt;

        return $firstFeedSourceCreated;
    }

    /**
     * Retrieve feed sources for specific conditions.
     *
     * @since [*next-version*]
     *
     * @param array|int|null $args The maximal amount of feed sources to retrieve.
     *  If array, will be treated as query args, and merged with defaults.
     * @return WP_Post[]|\Traversable A list of feed sources that obey the specified conditions.
     */
    protected function _getFeedSources($args = null)
    {
        if (!is_array($args)) {
            $args = is_null($args)
                    ? -1
                    : intval($args);
            $args = array('posts_per_page' => $args);
        }

        $args = array_merge_recursive_distinct(array(
            'post_type'         => $this->_getFeedSourcePostType(),
            'posts_per_page'    => -1,
            'orderby'           => 'date',
            'order'             => 'ASC',
        ), $args);

        $query = new \WP_Query($args);

        return $query->posts;
    }

    /**
     * Retrieves the first activation time value from the database.
     *
     * @since [*next-version*]
     *
     * @return int|null
     */
    protected function _getFirstActivationTimeDb()
    {
        $optionName =  $this->_getFirstActivationTimeOptionName();
        $value = get_option($optionName, null);

        if (!is_null($value )){
            $value = intval($value);
        }

        return $value;
    }

    /**
     * Sets the value of the option, which stores the first activation time in the database.
     *
     * @since [*next-version*]
     *
     * @param int $time The GMT time as a Unix timestamp.
     * @return bool True if time set successfully, false otherwise.
     */
    protected function _setFirstActivationTimeDb($time)
    {
        $optionName =  $this->_getFirstActivationTimeOptionName();

        return update_option($optionName, $time);
    }

    /**
     * Retrieves the name of the option, which stores the first activation time.
     *
     * @since [*next-version*]
     *
     * @return string Name of the option.
     */
    protected function _getFirstActivationTimeOptionName()
    {
        if (is_null($this->firstActivationTimeOptionName)) {
            $this->firstActivationTimeOptionName =
                    $this->getPlugin()->getCode() . static::FIRST_ACTIVATION_TIME_OPTION_SUFFIX;
        }

        return $this->firstActivationTimeOptionName;
    }

    /**
     * Records the current time as first activation time, if not already recorded.
     *
     * @since [*next-version*]
     *
     * @return LeaveReviewNotification This instance.
     */
    protected function _recordCurrentTime()
    {
        $time = $this->_getFirstActivationTimeDb();
        if (empty($time)) {
            $time = $this->_getCurrentTimestampGmt();
            $this->_setFirstActivationTimeDb($time);
        }

        return $this;
    }

    /**
     * Retrieves the current timestamp in the GMT zone.
     *
     * @since [*next-version*]
     *
     * @return int The number of seconds from the start of the Unix epoch, GMT.
     */
    protected function _getCurrentTimestampGmt()
    {
        return intval(current_time('timestamp', true));
    }

    /**
     * Retrieves the name of the feed source post type.
     *
     * @since [*next-version*]
     *
     * @return string The post type name of the feed source post type.
     */
    protected function _getFeedSourcePostType()
    {
        return $this->getPlugin()->getFeedSourcePostType();
    }
}
