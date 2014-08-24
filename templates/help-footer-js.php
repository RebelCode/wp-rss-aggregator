<?php
/**
 * Template for WP RSS Aggregator tooltip JavaScript for the footer.
 * 
 * @package   WPRSSAggregator
 * @author    Jean Galea <info@wprssaggregator.com>
 * @copyright Copyright (c) 2012-2014, Jean Galea
 * @link      http://www.wprssaggregator.com/
 * @license   http://www.gnu.org/licenses/gpl.html
 */
?>
<script type="text/javascript" id="<?php echo WPRSS_Help::get_instance()->prefix('footer-js') ?>">
	(function($, document, window) {
		$(function() {
			$(document).tooltip({
				items: '.<?php echo $vars['tooltip_handle_class'] ?>',
				content: function(){
					var $this = $(this);
					return $($this.attr('href')).html();
				}
			});
		});
	})(jQuery, document, top, undefined);
</script>