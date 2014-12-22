jQuery( document ).ready( function($) {

	manage_license = function() {
		var button = $(this),
			action = button.hasClass('button-activate-license') ? 'activate' : 'deactivate',
			button_orig_label = button.attr('value'),
			addon = button.attr('name').split('_', 3)[1], // Name has form "wprss_ftp_license_deactivate"; grab the "ftp" part.
			license = $('#wprss-' + addon + '-license-key').val(),
			nonce = $('#wprss_' + addon + '_license_nonce').val();

		button.attr('disabled', true);
		button.attr('value', action === 'activate' ? 'Activating...' : 'Deactivating...');

		$.ajax({
			url: ajaxurl,
			data: {
				action: 'wprss_ajax_manage_license',
				event: action,
				addon: addon,
				license: license,
				nonce: nonce
			},
			success: function( data, status, jqXHR) {
				var response = JSON.parse(data),
					td = button.parent();

				// Inject the new HTML we got to update the UI and hook up the onClick handler.
				if (response.html !== undefined) {
					td.empty();
					td.append(response.html);
					td.children('.button-activate-license').click(manage_license);
					td.children('.button-deactivate-license').click(manage_license);
				}

				// There was an error.
				if (response.error !== undefined) {
					console.log('There was an error: ' + response.error);
				}
			},
			error: function ( error ) {
				console.log('Error: ', error);
				button.attr('disabled', false);
				button.attr('value', button_orig_label);
			}
		});
	};

	$('.button-activate-license').click(manage_license);
	$('.button-deactivate-license').click(manage_license);

});