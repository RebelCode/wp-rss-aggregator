(function ($) {

    $(document).ready(function () {
        // Toggle the log options when the "Show options" link is clicked
        $('#wprss-error-log-options-link').click(function (e) {
            $('#wprss-error-log-options').slideToggle(200);
            e.preventDefault();
        });
    });

})(jQuery);
