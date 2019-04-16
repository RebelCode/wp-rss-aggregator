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

    $.ajax({
      type: 'POST',
      url,
      data: JSON.stringify(params),
      contentType: 'application/json',
    }).done((data) => {
      $targetEl.replaceWith(data.html)
    })
  }

  const handleClick = function ($link) {
    const $targetEl = $link.closest('[data-template-ctx]')

    const template = $targetEl.data('wpra-template')
    const templateOptions = $targetEl.data('template-ctx')

    let options = Object.assign({}, {
      template
    }, JSON.parse(atob(templateOptions)))
    options['page'] = $link.data('wpra-page')

    fetchList($targetEl, options)
  }

  $('body').on('click', 'a[data-wpra-page]', function (e) {
    e.preventDefault()
    handleClick($(this))
  });
})
