jQuery( document ).ready( function($) {

	onActivateClick = function() {
		// The button name is of form "wprss_ftp_license_deactivate", so we grab the "ftp" part.
		var addon = $(this).attr('name').split('_', 3)[1];

		$(this).attr('disabled', true);
		$(this).attr('value', 'Activating...');

		manageLicense( 'activate', addon);
	};

	onDeactivateClick = function() {
		// The button name is of form "wprss_ftp_license_deactivate", so we grab the "ftp" part.
		var addon = $(this).attr('name').split('_', 3)[1];

		$(this).attr('disabled', true);
		$(this).attr('value', 'Deactivating...');

		manageLicense( 'deactivate', addon);
	};

	$('.button-activate-license').each(function() {
		// Proxy/hitch the this var so it's available in the handler.
		$(this).click( $.proxy(onActivateClick, $(this)) );
	});
	$('.button-deactivate-license').each(function() {
		// Proxy/hitch the this var so it's available in the handler.
		$(this).click( $.proxy(onDeactivateClick, $(this)) );
	});

	manageLicense = function(action, addon) {
		var license = $('#wprss-' + addon + '-license-key').val(),
			nonce = $('#wprss_' + addon + '_license_nonce').val();

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
					button = $('[name="wprss_' + response.addon + '_license_activate"]'),
					td = button.parent();

				// Inject the new HTML we got to update the UI and hook up the onClick handler.
				if (response.html !== undefined) {
					td.empty();
					td.append(response.html);
					td.children('.button-activate-license').each(function() {
						$(this).click( $.proxy(onActivateClick, $(this)) );
					});
					td.children('.button-deactivate-license').each(function() {
						$(this).click( $.proxy(onDeactivateClick, $(this)) );
					});
				}

				// There was an error.
				if (response.error !== undefined) {
					console.log('There was an error: ' + response.error);
				}
			},
			error: function ( error ) {
				// Reactivate all buttons. User really should refresh, though.
				$('.button-process-license').attr('disabled', false);
			}
		});
	}

});