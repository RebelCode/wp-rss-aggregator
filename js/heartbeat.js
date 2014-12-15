(function($, wprss_admin_heartbeat){
	

	/**
	 * Returns the IDs of the feed sources shown on the current page
	 */
	var getFeedSourceIDS = function() {
		var ids = [];
		$('table.wp-list-table tbody tr').each( function(){
			if ( $(this).hasClass('no-items') ) return;
			ids.push( $(this).attr('id').split('-')[1] );
		});
		return ids;
	}


	/**
	 * Attach the heartbeat data
	 */
	var checkFeedSourcesUpdatingStatus = function() {
		var ids = getFeedSourceIDS();
		// If no feed sources found, do nothing. Performance boost
		if ( ids.length === 0 ) return;
		// Return the data
		return {
			action: 'feed_sources',
			params: ids
		};
	};



	/**
	 * Updates the feed source table using the heartbeat data.
	 */
	var updateFeedSourceTable = function(data) {
		if ( !data['wprss_feed_sources_data'] ) return;

		// Get the feed sources data
		var feed_sources = data['wprss_feed_sources_data'];
		// Iterate all the received feed source data
		for( id in feed_sources ) {
			var feed_source = feed_sources[id];
			var row = $('table.wp-list-table tbody tr.post-' + id);
			var updatesCol = row.find('td.column-updates');
			var itemsCol = row.find('td.column-feed-count');

			// Update the next update time
			updatesCol.find('code.next-update').text( feed_source['next-update'] );

			// Update the last update time
			if ( feed_source['last-update'] == '' )
				updatesCol.find('p.last-update-container').hide();
			else
				updatesCol.find('code.last-update').text( feed_source['last-update'] + ' ' + wprss_admin_heartbeat.ago );

			// Update the last update items count
			if ( feed_source['last-update-imported'] == '' )
				updatesCol.find('span.last-update-imported-container').hide();
			else
				updatesCol.find('code.last-update-imported').text( feed_source['last-update-imported'] );

			// Update the items imported count and the icon
			var icon = itemsCol.find('i.fa-spin');
			var itemCount = itemsCol.find('span.items-imported');

			// Update the count and the icon appropriately
			itemCount.text( feed_source['items'] );
			icon.toggleClass( 'wprss-show', feed_source['updating'] );
			
			
			


			// Update the error icon
			var errorsCol = row.find('td.column-errors');
			var errorIcon = errorsCol.find('i.fa');
			errorIcon.toggleClass( 'wprss-show', feed_source['errors'] !== '' );
		}

	};

	var wprssFeedSourceTableAjax = function(){
		var data = checkFeedSourcesUpdatingStatus();
		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: {
				action: 'wprss_feed_source_table_ajax',
				wprss_heartbeat: data
			},
			success: function(data, status, jqXHR){
				updateFeedSourceTable(data);
				setTimeout(wprssFeedSourceTableAjax, 5000);
			},
			dataType: 'json'
		});
	};
	
	
	$(document).ready( function(){
		wprssFeedSourceTableAjax();
	});
	

})(jQuery, wprss_admin_heartbeat);