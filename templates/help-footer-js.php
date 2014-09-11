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
			var tooltipHandleClass = '<?php echo isset( $vars['tooltip_handle_class'] ) ? $vars['tooltip_handle_class'] : '' ?>';
			
			// If class defined, register tooltips
			tooltipHandleClass.length && $(document).tooltip({
				items: '.'+tooltipHandleClass,
				content: function(){
					var $this = $(this);
					return $($this.attr('href')).html();
				}
			});
			
			// If class defined, prevent tooltip handles from changing URL
			tooltipHandleClass.length && $('.'+tooltipHandleClass).on('click', function(e) {
				e.preventDefault();
			});
		});
	})(jQuery, document, top, undefined);
</script>