require('css/src/pagination/index.scss')

jQuery(document).ready(($) => {
  const fetchList = function ($targetEl, params) {
    $targetEl.addClass('wpra-loading')

    $([document.documentElement, document.body]).animate({
      scrollTop: $targetEl.offset().top - 50
    }, 500);

    $.ajax(`${WpraPagination.baseUri}${params.template}`, {
      data: {
        page: params.page,
      }
    }).done((data) => {
      $targetEl.replaceWith(data.html)
    })
  }

  const handleClick = function ($link) {
    const $targetEl = $link.closest('[data-wpra-template]')

    const page = $link.data('wpra-page')
    const template = $targetEl.data('wpra-template')

    fetchList($targetEl, {
      page,
      template,
    })
  }

  $('body').on('click', 'a[data-wpra-page]', function (e) {
    e.preventDefault()
    handleClick($(this))
  });
})
