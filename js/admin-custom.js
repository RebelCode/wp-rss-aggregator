jQuery( document ).ready( function() { 

	// On TAB pressed when on title input field, go to URL input field
	jQuery('input#title').on( 'keydown', function( event ) {    
        
        if ( event.which == 9 ) {
            event.preventDefault();
            jQuery('input#wprss_url').focus();
        }
    }
    );	    

	// On TAB pressed when on description textarea field, go to Publish submit button
	jQuery('textarea#wprss_description').on( 'keydown', function( event ) {
                
        if ( event.which == 9 ) {
            event.preventDefault();
            jQuery('input#publish').focus();
        }
    }
    );	        
	
	// jQuery for 'Fetch Feed Items' Row Action in 'All Feed Sources' page
	jQuery('.wprss_ajax_action').click( function(){
		action_link = jQuery(this);
		id = action_link.attr('pid');
		url = action_link.attr('purl');
		jQuery.post(
			url, 
			{
				'action': 'wprss_fetch_feeds_action',
				'id':   id
			}, 
			function(response){
				action_link.text('Latest Feed Items successfully imported!');
			}
		);
		action_link.text('Please Wait ...');
		action_link.unbind('click');
	});
	
	// Make the number rollers change their value to empty string when value is 0, making
	// them use the placeholder.
	jQuery('.wprss-number-roller').on('change', function(){
		if ( jQuery(this).val() == 0 )
			jQuery(this).val('');
	});
});


/* JS for admin notice - Leave a review */

(function ($) {
    "use strict";
    $(function () {

        // Check to see if the Ajax Notification is visible
        if ($('#dismiss-ajax-notification').length > 0) {

            // If so, we need to setup an event handler to trigger it's dismissal
            $('#dismiss-ajax-notification').click(function (evt) {

                evt.preventDefault();

                // Initiate a request to the server-side
                $.post(ajaxurl, {

                    // The name of the function to fire on the server
                    action: 'wprss_hide_admin_notification',

                    // The nonce value to send for the security check
                    nonce: $.trim($('#ajax-notification-nonce').text())

                }, function (response) {

                    // If the response was successful (that is, 1 was returned), hide the notification;
                    // Otherwise, we'll change the class name of the notification
                    if ('1' === response) {
                        $('#ajax-notification').fadeOut('slow');
                    } else {

                        $('#ajax-notification')
                            .removeClass('updated')
                            .addClass('error');

                    } // end if

                });

            });

        } // end if

    });
}(jQuery));