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

			// Update the last update time and item count
			if ( feed_source['last-update'] == '' ) {
				updatesCol.find('p.last-update-container').hide();
			} else {
				updatesCol.find('.last-update-time').text(feed_source['last-update'] + ' ' + wprss_admin_heartbeat.ago);
				updatesCol.find('.last-update-num-items').text( feed_source['last-update-imported'] );
				updatesCol.find('p.last-update-container').show();
			}

			// Update the items imported count and the icon
			var itemCount = itemsCol.find('span.items-imported');

			// Update the count and the icon appropriately
			itemCount.text( feed_source['items'] );

			// Toggle the row's updating class
			row.toggleClass('wpra-feed-is-updating', !!feed_source['updating']);

			// Toggle the "has imported items" class depending on the number of imported items
			itemsCol.find('.items-imported-link').toggleClass('has-imported-items', feed_source['items'] > 0);
			// Hide the "Delete" row action for items if there are no imported items
			itemsCol.find('.row-actions .purge-posts').toggle(feed_source['items'] > 0);

			// Update the error icon
			var errorIcon = itemsCol.find('i.wprss-feed-error-symbol');
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
				setTimeout(wprssFeedSourceTableAjax, 1000);
			},
			dataType: 'json'
		});
	};
	
	
	$(document).ready( function(){
		wprssFeedSourceTableAjax();
	});
	

})(jQuery, wprss_admin_heartbeat);
