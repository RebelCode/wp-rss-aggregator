var WPRSS_TMCE_PLUGIN_ID = 'wprss';

(function() {
	tinymce.create( 'tinymce.plugins.' + WPRSS_TMCE_PLUGIN_ID, {
		init : function( ed, url ) {
			ed.addButton( WPRSS_TMCE_PLUGIN_ID, {
				title : 'WPRSS Aggregator shortcode',
				image : url + '/../images/icon-adminpage32.png',
				onclick : function() {
					idPattern = /(?:(?:[^v]+)+v.)?([^&=]{11})(?=&|$)/;
					var vidId = prompt("WP RSS Aggregator Video", "Choose feed source");
					var m = idPattern.exec(vidId);
					if (m != null && m != 'undefined')
						ed.execCommand('mceInsertContent', false, '[wprss source="'+m[1]+'"]');
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
})();