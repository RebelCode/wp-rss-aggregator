jQuery( document ).ready( function() { 

	// On TAB pressed when on title input field, go to URL input field
	jQuery('input#title').on( 'keydown', function( event ) {
        event.preventDefault();
        
        if ( event.which == 9 ) {
            jQuery('input#wprss_url').focus();
        }
    }
    );	    

	// On TAB pressed when on description textarea field, go to Publish submit button
	jQuery('textarea#wprss_description').on( 'keydown', function( event ) {
        event.preventDefault();
        
        if ( event.which == 9 ) {
            jQuery('input#publish').focus();
        }
    }
    );	        
});
