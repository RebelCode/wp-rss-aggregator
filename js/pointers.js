function wprssTrackingOptAJAX( opted ) {
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'wprss_tracking_ajax_opt',
			opted: opted
		},
		success: function( data, t, f ) {
			console.log( data );
			console.log( t );
			console.log( f );
		}
	});
}