<?php

/**
 * This is a module responsible for facilitating the "Leave a Review" notification.
 */

if (!defined('WPRSS_LEAVE_REVIEW_NOTIFICATION_DELAY')) {
    define('WPRSS_LEAVE_REVIEW_NOTIFICATION_DELAY', 60 * 60 * 24 * 7 * 4 * 2); // 2 months
}

add_action('wprss_init', function() {
    wprss()->getLeaveReviewNotification();
});
