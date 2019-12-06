(function ($, Config) {
    $(document).ready(function () {
        // Double confirmation for the reset operations
        $('#wpra-delete-items-form, #wpra-reset-settings-form').on('submit', function (e) {
            e.preventDefault();

            var confirmation = confirm(Config.message);
            if (confirmation == true) {
                $(this).submit();
            }
        });
    });
})(jQuery, WpraResetTool);
