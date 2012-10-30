jQuery( document ).ready( function() { 

	// On TAB pressed when on title input field, go to URL input field
	jQuery('input#title').on( 'keydown', function( event ) {
        event.preventDefault();
        
        if ( event.which == 9 ) {
            //$('h1.entry-title').css('color', 'red');
            jQuery('input#wprss_url').focus();

            console.log('tab pressed');
        }
    }
    );	    

	// On TAB pressed when on description textarea field, go to Publish submit button
	jQuery('textarea#wprss_description').on( 'keydown', function( event ) {
        event.preventDefault();
        
        if ( event.which == 9 ) {
            //$('h1.entry-title').css('color', 'red');
            jQuery('input#publish').focus();

            console.log('tab pressed');
        }
    }
    );	        
});
