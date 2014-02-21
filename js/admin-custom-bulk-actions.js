/**
 * Adds and manages custom bulk actions for the Feed Sources page.
 * 
 * @since 2.5
 */
(function($){
	
	$(document).ready( function(){
		var bulk_actions_select = $( 'select[name="action"]' );
		var bulk_actions_trash = bulk_actions_select.find( "option[value='trash']" );
		
		var activate_action = $( '<option>' ).attr( 'value', 'activate' ).text( 'Activate' ).insertBefore( bulk_actions_trash );
		var pause_action = $( '<option>' ).attr( 'value', 'pause' ).text( 'Pause' ).insertBefore( bulk_actions_trash );
	});


})(jQuery);