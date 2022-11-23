(function ($, Config) {

  $(document).ready(() => {
    const survey = $('.wpra-survey-2022')
    const target = $('.wp-header-end')

    if (target.length) {
      survey.insertAfter(target.first())
    }

    $('.wpra-survey-2022__close').on('click', () => {
      survey.remove()

      $.ajax({
        url: Config.ajaxUrl,
        type: 'POST',
        data: {
          action: Config.action,
          _wpnonce: Config.nonce,
        },
        error: (jqXHR, textStatus, errorThrown) => {
          console.error(errorThrown, textStatus, jqXHR)
        },
      })
    })
  })

})(jQuery, WpraSurvey2022)
