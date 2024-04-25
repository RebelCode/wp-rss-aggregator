/**
 * Notifications client-side handling.
 * Depends on Aventura.Wp.Admin.Notices.
 */

(function($) {
    var globalVars = adminNoticeGlobalVars || {};
    var notices = Aventura.Wp.Admin.Notices.getGlobal();
    notices.setOptions(globalVars).setOptions("ajax_url", ajaxurl);
    notices.attach();

    $(".wpra-v5-notice-close").click(function() {
        const noticeEl = $(this).closest(".wpra-v5-notice");
        const noticeId = noticeEl.data("notice-id");
        const nonce = noticeEl.find(".wpra-v5-notice-nonce").val();

        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "wprss_dismiss_v5_notice",
                nonce: nonce,
                notice: noticeId
            },
            success: function(data) {
                if (data === "OK") {
                    noticeEl.slideUp("fast", function() {
                        noticeEl.remove();
                    });
                }
            }
        });
    });
})(jQuery);
