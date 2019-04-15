require('css/src/pagination/index.scss')

jQuery(document).ready(($) => {
  const fetchList = function ($targetEl, params) {
    $targetEl.addClass('wpra-loading')

    $([document.documentElement, document.body]).animate({
      scrollTop: $targetEl.offset().top - 50
    }, 500);

    const template = params.template
    delete params.template

    let url = WpraPagination.baseUri.replace('%s', template)

    $.ajax(url, {
      data: params
    }).done((data) => {
      $targetEl.replaceWith(data.html)
    })
  }

  const handleClick = function ($link) {
    const $targetEl = $link.closest('[data-template-options]')

    const page = $link.data('wpra-page')
    const template = $targetEl.data('wpra-template')

    const templateOptions = $targetEl.data('template-options')

    const options = Object.assign({}, {
      page,
      template
    }, JSON.parse(atob(templateOptions)))

    fetchList($targetEl, options)
  }

  $('body').on('click', 'a[data-wpra-page]', function (e) {
    e.preventDefault()
    handleClick($(this))
  });
})
