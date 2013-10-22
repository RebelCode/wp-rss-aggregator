var WPRSS_TMCE_PLUGIN_ID = 'wprss';

jQuery( document ).ready( function($) {


	window.WP_RSS_Editor = new function() {
		// Keep a reference to the current object
		var base = this;
		var dialog = null;
		var dialog_head = null;
		var dialog_head_close = null;
		var dialog_inside = null;

		var close = function( e ) {
			overlay.fadeOut();
			dialog_inside.empty();
		}

		base.init = function() {
			overlay = $('<div id="wprss-overlay"></div>');
			dialog = $('<div id="wprss-editor-dialog" class="postbox"></div>');

			dialog_head = $('<div class="wprss-dialog-header"> <h1>WPRSS Aggregator Shortcode</h1> </div>');
			dialog_head_close = $('<span class="close-btn"></span>').html('&times;').appendTo( dialog_head );
			dialog_inside = $('<div class="wprss-dialog-inside"></div>');
			dialog.append( dialog_head );
			dialog.append( dialog_inside );

			overlay.hide().appendTo('body');
			dialog.appendTo(overlay);

			overlay.click( close );
			dialog_head_close.click( close );

			dialog.on( 'click', function( e ) {
				e.stopPropagation();
			});
		};


		base.getDialog = function() {
			overlay.show();

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wprss_editor_dialog'
				},
				success: function( data, status, jqXHR) {
					if ( data.length > 0 ) {
						dialog_inside.html( data );
					}
				}
			});

			
		};
	}


	WP_RSS_Editor.init();




	tinymce.create( 'tinymce.plugins.' + WPRSS_TMCE_PLUGIN_ID, {
		// INITIALIZE THE BUTTON
		init : function( ed, url ) {
			// Add the button
			ed.addButton( WPRSS_TMCE_PLUGIN_ID, {
				title : 'WPRSS Aggregator shortcode',
				image : url + '/../images/icon-adminpage32.png',
				onclick : function() {
					idPattern = /(?:(?:[^v]+)+v.)?([^&=]{11})(?=&|$)/;
					WP_RSS_Editor.getDialog();
					/*
					var vidId = prompt("WP RSS Aggregator", "Choose feed source");
					var m = idPattern.exec(vidId);
					if (m != null && m != 'undefined')
						ed.execCommand('mceInsertContent', false, '[wprss source="'+m[1]+'"]');
					*/
				}
			});
		},
		createControl : function( n, cm ) {
			return null;
		},
		getInfo : function() {
			return {
				longname : "WPRSS Aggregator Shortcode",
				author : 'John Galea',
				authorurl : 'http://profiles.wordpress.org/jeangalea/',
				infourl : 'http://www.wprssaggregator.com/',
				version : "1.0"
			};
		}
	});
	tinymce.PluginManager.add( WPRSS_TMCE_PLUGIN_ID, tinymce.plugins.wprss );
});