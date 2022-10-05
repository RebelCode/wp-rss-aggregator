jQuery(document).ready(function ($) {
  if ($.fn.colorbox) {
    const links = $('.colorbox')
    links.colorbox({
      iframe: true,
      width: '80%',
      height: '80%',
    })
  }
})
