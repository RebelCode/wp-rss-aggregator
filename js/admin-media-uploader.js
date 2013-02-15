/**
 * Main jQuery media file to handle uploading the default image thumbnail via the WP 3.5 media uploader
 * Based on http://www.webmaster-source.com/2013/02/06/using-the-wordpress-3-5-media-uploader-in-your-plugin-or-theme/
 * and https://github.com/thomasgriffin/New-Media-Image-Uploader/blob/master/js/media.js
 */
jQuery(document).ready(function($){

    // Prepare the variable that holds our custom media manager.
    var custom_media_frame;
    
    // Bind to our click event in order to open up the new media experience.
    $( '#default-thumbnail-button' ).click( function( e ) {
        // Prevent the default action from occuring.
        e.preventDefault();
 
        // If the uploader object has already been created, re-open it
        if ( custom_media_frame ) {
            custom_media_frame.open();
            return;
        }
 
        // The media frame doesn't exist let, so let's create it with some options.
        custom_media_frame = wp.media.frames.file_frame = wp.media({
            title: 'Choose Default Thumbnail Image',
            button: {
                text: 'Choose image'
            },
            multiple: false
        });
 
        // When a file is selected, grab the URL and set it as the text field's value
        custom_media_frame.on( 'select', function() {
            // Grab our attachment selection and construct a JSON representation of the model.
            attachment = custom_media_frame.state().get( 'selection' ).first().toJSON();
            // Send the attachment URL to our custom input field via jQuery.
            $( '#default-thumbnail' ).val( attachment.url );
        });
 
        // Now that everything has been set, let's open up the frame.
        custom_media_frame.open();
 
    });
});



