<?php

namespace Aventura\Wprss\Core\Component;

use Aventura\Wprss\Core;

/**
 * Component responsible for notices in the backend.
 *
 * @since 4.10
 */
class AdminAjaxNotices extends Core\Plugin\ComponentAbstract
{

    /**
     * Retrieve the notice collection.
     *
     * @see wprss_admin_notice_get_collection()
     *
     * @since 4.10
     *
     * @return \WPRSS_Admin_Notices The notice collection object.
     */
    public function getNoticeCollection()
    {
        return wprss_admin_notice_get_collection();
    }

    /**
     * Add a notice.
     *
     * @see wprss_admin_notice_add()
     *
     * @param array $notice Data of the notice
     *
     * @return bool|WP_Error True if notice added, false if collection unavailable, or WP_Error if something went wrong.
     */
    public function addNotice($notice)
    {
        return wprss_admin_notice_add($notice);
    }
}
