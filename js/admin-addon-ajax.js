jQuery( document ).ready( function($) {

	$('.ajax-close-addon-notice').click( function() {
		addon = $(this).attr( 'data-addon' );
		notice = $(this).attr( 'data-notice' );
		element = $(this).parent().parent();
		if ( addon !== false && typeof addon !== 'undefined' && notice !== false && typeof notice !== 'undefined' ) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wprss_dismiss_addon_notice',
					addon: addon,
					notice: notice,
				},
				success: function( data, status, jqXHR) {
					if ( data === 'true' ) {
						element.slideUp( 'fast', function(){
							element.remove();
						});
					}
				}
			});
			$(this).text('Please wait ...');
		}
	});

});