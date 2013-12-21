// jQuery for 'Fetch Feed Items' Row Action in 'All Feed Sources' page
function fetch_items_row_action_callback(){
    var link = jQuery(this);
    var original_text = link.text();
    var id = link.attr('pid');
    var url = link.attr('purl');
    jQuery.post(
        url, 
        {
            'action': 'wprss_fetch_feeds_row_action',
            'id':   id
        }, 
        function(response){
            link.text('Feed items imported!');
            setTimeout( function(){
                link.text( original_text ).click( fetch_items_row_action_callback );
            }, 3500 );
        }
    );
    link.text('Please wait ...');
    link.unbind('click');
};



jQuery(window).load( function(){


    function wprssParseDate(str){
        var t = str.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if( t!==null ){
            var d=+t[1], m=+t[2], y=+t[3];
            var date = new Date(y,m-1,d);
            if( date.getFullYear() === y && date.getMonth() === m-1 ){
                return date;   
            }
        }
        return null;
    }


    var WPRSS_DATE_FORMAT = 'dd/mm/yy';
    var WPRSS_TIME_FORMAT = 'HH:mm:ss';
    var WPRSS_NOW = new Date();
    var WPRSS_NOW_UTC = new Date(
        WPRSS_NOW.getUTCFullYear(),
        WPRSS_NOW.getUTCMonth(),
        WPRSS_NOW.getUTCDate(),
        WPRSS_NOW.getUTCHours(),
        WPRSS_NOW.getUTCMinutes(),
        WPRSS_NOW.getUTCSeconds()
    );

    // Set datepickers
    jQuery.datepicker.setDefaults({
        dateFormat: WPRSS_DATE_FORMAT,
    });
    jQuery.timepicker.setDefaults({
        controlType: 'slider',
        timezone: 0,
        timeFormat: WPRSS_TIME_FORMAT,
    });
    jQuery('.wprss-datetimepicker').datetimepicker();
    jQuery('.wprss-datetimepicker-from-today').datetimepicker({ minDate: WPRSS_NOW_UTC });


    jQuery('.wprss-datepicker, .wprss-datepicker-from-today').focusout( function(){
        val = jQuery(this).val();
        if ( val !== '' && wprssParseDate( val ) === null ) {
            jQuery(this).addClass('wprss-date-error');
        } else jQuery(this).removeClass('wprss-date-error');
    });

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

	jQuery('.wprss_ajax_action').click( fetch_items_row_action_callback );
	
	// Make the number rollers change their value to empty string when value is 0, making
	// them use the placeholder.
	jQuery('.wprss-number-roller').on('change', function(){
		if ( jQuery(this).val() == 0 )
			jQuery(this).val('');
	});


    /* JS for admin notice - Leave a review */


    // Check to see if the Ajax Notification is visible
    if ( jQuery('#dismiss-ajax-notification').length > 0 ) {
        NOTIFICATION = jQuery('#ajax-notification');
        NOTIFICATION_DISMISS = jQuery('#dismiss-ajax-notification');
        NOTIFICATION_DISMISS.click( function(evt){
            evt.preventDefault();
            evt.stopPropagation();

            jQuery.post(ajaxurl, {
                // The name of the function to fire on the server
                action: 'wprss_hide_admin_notification',
                // The nonce value to send for the security check
                nonce: jQuery.trim( jQuery('#ajax-notification-nonce').text() )
            }, function (response) {
                // If the response was successful (that is, 1 was returned), hide the notification;
                // Otherwise, we'll change the class name of the notification
                if ( response !== '1' ) {
                    NOTIFICATION.removeClass('updated').addClass('error');
                } // end if
            });

            NOTIFICATION.fadeOut(400);
        });
    }


    if ( jQuery('#wprss_tracking_notice') ) {
        
    }



    // GENERATES A RANDOM STRING FOR THE SECURE RESET CODE FIELD
    jQuery('#wprss-secure-reset-generate').click( function(){
        jQuery('input#wprss-secure-reset-code').val( Math.random().toString(36).substr(2) );
    });


});


/**
 * WP-like collapsing settings in metabox 
 */
(function($){
    $(window).load( function(){

        // Initialize all collapsable meta settings
        $('.wprss-meta-slider').each(function(){
            // Get all required elements
            var slider = $(this);
            var viewerID = slider.attr('data-collapse-viewer');
            var viewer = $( '#' + viewerID );
            var editLink = viewer.next();

            var hybrid = slider.attr('data-hybrid');
            var fields = ( typeof hybrid !== 'undefined' && hybrid !== false ) ? $( hybrid ) : slider.find('*').first();

            var labelAttr = slider.attr('data-label');
            var label = ( typeof labelAttr !== 'undefined' && labelAttr !== false ) ? $( labelAttr ) : null;

            // The controller is the field that, when using hybrid fields, determines if the value is empty or not
            var controllerAttr = slider.attr( 'data-empty-controller' );
            var controller = ( typeof controllerAttr !== 'undefined' && controllerAttr !== false ) ? $( controllerAttr ) : null;

            var labelWhenEmpty = null;
            if ( label !== null ) {
                var whenEmpty = label.attr('data-when-empty');
                labelWhenEmpty = ( typeof whenEmpty !== 'undefined' && whenEmpty !== false ) ? whenEmpty : label.text();
            }

            var defaultValue = slider.attr('data-default-value');
            // Edit link opens the settings
            editLink.click(function( e ){
                // If not open already, open it
                if ( !slider.hasClass('wprss-open') )
                    slider.slideDown().addClass('wprss-open');
                e.preventDefault();
                fields.each( function(){
                    $(this).attr( 'data-old-value', $(this).val() );
                });
            });

            // The update function
            var update = function(){
                // On click, get the value of the fields
                var val = '';
                fields.each( function(){
                    if ( $(this).is('select') ) {
                        val += ' ' + $( this ).find('option:selected').text();
                    }
                    else val += ' ' + $(this).val();
                });
                // check the controller
                var controllerVal = '=';
                if ( controller !== null ) {
                    if ( controller.is('select') ) {
                        controllerVal = ' ' + controller.find('option:selected').text();
                    } else controllerVal = ' ' + controller.val();
                }
                // If empty, use the default value
                if ( val.trim() === '' || controllerVal.trim() === '' ) {
                    val = defaultValue;
                    // If the label is set, and it has alternate text for empty values, switch its text and attr
                    if ( label !== null ) {
                        var whenEmpty = label.attr('data-when-empty');
                        var labelWhenEmpty = ( typeof whenEmpty !== 'undefined' && whenEmpty !== false ) ? whenEmpty : null;
                        if ( labelWhenEmpty !== null ) {
                            label.attr( 'data-when-not-empty', label.text() ).text( labelWhenEmpty );
                        }
                    }
                }
                // Otherwise if the value is not empty, and the label is set to its empty counterpart, switch it back
                else {
                    if ( label !== null ) {
                        var whenNotEmpty = label.attr('data-when-not-empty');
                        var labelWhenNotEmpty = ( typeof whenNotEmpty !== 'undefined' && whenNotEmpty !== false ) ? whenNotEmpty : null;
                        if ( labelWhenNotEmpty !== null ) {
                            label.attr( 'data-when-empty', label.text() ).text( labelWhenNotEmpty );
                        }
                    }
                }
                // Set the text of the viewer to the value
                viewer.text( val );
            };

            // Create the OK Button
            var okBtn = $('<a>').addClass('wprss-slider-button button-secondary').text('OK').click( update );
            // Create the Cancel Button
            var cancelBtn = $('<a>').addClass('wprss-slider-button').text('Cancel').click( function() {
                fields.each( function(){
                    $(this).val( $(this).attr( 'data-old-value' ) );
                    $(this).removeAttr( 'data-old-value' );
                });
            });

            // Add the buttons
            slider.append( $('<br>') ).append( $('<br>') ).append( okBtn ).append( cancelBtn );

            // Make both buttons close the div
            slider.find('.wprss-slider-button').click( function(){
                slider.slideUp().removeClass('wprss-open');
            });

            // Update when ready
            update();
        });

    });
})(jQuery);

// Utility string trim method, if it does not exist
if ( !String.prototype.trim ) {
    String.prototype.trim = function(){
        return this.replace(/^\s+|\s+$/g, '');
    };
}