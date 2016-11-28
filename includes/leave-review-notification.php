<?php

/**
 * This is a module responsible for facilitating the "Leave a Review" notification.
 */

add_action('wprss_init', function() {
    wprss()->getLeaveReviewNotification();
});
