/**
 * Adds and manages custom bulk actions for the Feed Items page.
 *
 * @since 4.7
 */
(function($, wprss_admin_bulk_feed_item){

	$(document).ready( function(){
		var bulk_actions_select = $( 'select#bulk-action-selector-top, select#bulk-action-selector-bottom' );
		var bulk_actions_edit = bulk_actions_select.find( "option[value='edit']" );

		$( '<option>' ).attr( 'value', 'trash' ).text( wprss_admin_bulk_feed_item.trash ).insertBefore( bulk_actions_edit );
		bulk_actions_edit.remove();
	});

})(jQuery, wprss_admin_bulk_feed_item);
