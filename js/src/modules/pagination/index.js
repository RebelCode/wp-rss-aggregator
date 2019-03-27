jQuery(document).ready(($) => {
  const fetch = function ($targetEl, page) {
    $.ajax()
  }
  const handleClick = function ($link) {
    const targetPage = $link.data('wpra-page')
    const targetEl = $()
    console.warn('paginate to ', targetPage)
  }

  $('body').on('click', 'a[data-wpra-page]', function (e) {
    e.preventDefault()
    handleClick($(this))
  });
})
