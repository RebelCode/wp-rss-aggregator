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
    const NOTICE_ID_SUFFIX = '_leave_review';
    const REVIEW_PAGE_URL = 'https://wordpress.org/support/plugin/wp-rss-aggregator/reviews/';

    protected $firstActivationTime;
    protected $firstActivationTimeOptionName;
    protected $noticeId;

    /**
     * Runs when this component is initialized.
     *
     * @since [*next-version*]
     */
    public function hook()
    {
        $this->init();

        $hookName = sprintf('!activate_%1$s', $this->getPlugin()->getBasename());
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
     * Runs when all plugins have finished loading.
     *
     * @since [*next-version*]
     */
    public function init()
    {
        $this->addLeaveReviewNotice();
    }

    /**
     * Adds the notification.
     *
     * @since [*next-version*]
     *
     * @return LeaveReviewNotification This instance.
     */
    public function addLeaveReviewNotice()
    {
        $notices = $this->_getNoticesComponent();

        $noticeParams = array(
            'id'                => $this->getNoticeId(),
            'content'           => $this->getNoticeContent(),
            'condition'         => $this->getNoticeCondition()
        );
        $this->event('leave_review_notice_before_add', array('notice_params' => &$noticeParams));

        $notices->addNotice($noticeParams);
    }

    /**
     * Retrieve the ID of the "Leave a Review" admin notice.
     *
     * @since [*next-version*]
     *
     * @return string The ID of the notice.
     */
    public function getNoticeId()
    {
        $idSuffix = static::NOTICE_ID_SUFFIX;
        if (is_null($this->noticeId)) {
            $this->noticeId = $this->getPlugin()->getCode() . $idSuffix;
        }

        $noticeId = $this->noticeId;
        $this->event('leave_review_notice_id', array(
            'notice_id'         => &$noticeId,
            'id_suffix'         => $idSuffix
        ));

        return $noticeId;
    }

    /**
     * Retrieve the content for the "Leave a Review" notice.
     *
     * @since [*next-version*]
     *
     * @return string The content for the notice.
     */
    public function getNoticeContent()
    {
        $content = wpautop(sprintf(
            'Looks like you have been using %1$s for a while! Please consider <a href="%2$s" target="_blank">leaving a review</a>.',
            $this->getPlugin()->getName(),
            $this->getReviewPageUrl()
        ));

        $this->event('leave_review_notification_content', array(
            'content'   => &$content
        ));

        return $content;
    }

    /**
     * Retrieve the condition for the notice to show.
     *
     * @since [*next-version*]
     *
     * @return callable The callable that determines whether or not to display the notice.
     */
    public function getNoticeCondition()
    {
        $condition = $this->_createCommand(array(
            'function'          => array($this, 'isShowLeaveReviewNotification')
        ));

        $this->event('leave_review_notice_condition', array('condition' => &$condition));

        return $condition;
    }

    /**
     * Determines if the notice is allowed to be displayed on the current page.
     *
     * @since [*next-version*]
     *
     * @return bool True if the notice is allowed to be displayed on the current page; false otherwise.
     */
    public function isShowLeaveReviewNotification()
    {
        return $this->isWprssPage();
    }

    /**
     * Determines if the curren page is related to WPRSS.
     *
     * @since [*next-version*]
     *
     * @return bool True if the current page is related to WPRSS; false otherwise.
     */
    public function isWprssPage()
    {
        return $this->getAdminHelper()->isWprssPage();
    }

    /**
     * Retrieves the URL of the page where to send visitors to leave a review.
     *
     * @since [*next-version*]
     *
     * @return string The page URL.
     */
    public function getReviewPageUrl()
    {
        $url = static::REVIEW_PAGE_URL;
        $this->event('leave_review_page_url', array('url' => &$url));

        return $url;
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
        $wasCalculated = false;
        if (empty($time)) {
            $time = $this->_calculateFirstActivationTime();
            $this->_setFirstActivationTimeDb($time);
            $wasCalculated = true;
        }

        $formatted = is_null($format)
                ? $time
                : date($format, $time);

        $this->event('leave_review_first_activation_time', array(
            'time'              => $time,
            'format'            => $format,
            'time_formatted'    => &$formatted,
            'was_calculated'    => $wasCalculated
        ));

        return $formatted;
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

    /**
     * Retrieve the component responsible for admin AJAX notices.
     *
     * @since [*next-version*]
     *
     * @return AdminAjaxNotices The component instance.
     */
    protected function _getNoticesComponent()
    {
        return $this->getPlugin()->getAdminAjaxNotices();
    }

    /**
     * Retrieve the admin helper singleton.
     *
     * @since [*next-version*]
     *
     * @return AdminHelper The helper singleton instance.
     */
    public function getAdminHelper()
    {
        return $this->getPlugin()->getAdminHelper();
    }

    /**
     * Creates a callable command instance.
     *
     * @since [*next-version*]
     *
     * @param array|callable $data See {@see Core\Model\Command}.
     * @return Core\Model\Command|callable See {@see Core\Model\Command}.
     */
    protected function _createCommand($data)
    {
        return $this->getAdminHelper()->createCommand($data);
    }
}
