(function ($, Config) {
  const EndDate = new Date(Config.endDate)

  function App() {
    return h('div', {class: "wpra-bf23"}, [
      Icon(),
      Message(),
      Timer(),
      BuyButton(),
      DismisBtn(),
    ])
  }

  function Message() {
    return h('div', {class: "wpra-bf23__msg"}, [
      t("Get "),
      h('a', {href: Config.url, target: "_blank"}, [t(Config.discount + " OFF")]),
      t(" WP RSS Aggregator's premium plans. Coupon code: "),
      h('span', {class: "wpra-bf23__coupon"}, [Config.coupon]),
    ])
  }

  function Icon() {
    return h('div', {class: "wpra-bf23__icon"}, [
      h('i', {class: "dashicons dashicons-star-filled"}),
    ])
  }

  function Timer() {
    return h('div', {class: "wpra-bf23__timer"}, [
      TimerNum("days", "Days"),
      TimerNum("hours", "Hours"),
      TimerNum("mins", "Minutes"),
      TimerNum("secs", "Seconds"),
    ])
  }

  function TimerNum(unit, label) {
    return h('div', {class: "wpra-bf23__timer__cell"}, [
      h('div', {class: "wpra-bf23__timer__num", "data-unit": unit}, ["--"]),
      h('div', {class: "wpra-bf23__timer__label"}, [label]),
    ])
  }

  function BuyButton() {
    return h('a', {class: "wpra-bf23__btn", href: Config.url, target: "_blank"}, [
      h('i', {class: "dashicons dashicons-star-filled"}),
      t("Upgrade at " + Config.discount + " off"),
    ])
  }

  function DismisBtn() {
    return h('button', {class: "wpra-bf23__dismiss"}, [
      h('i', {class: "dashicons dashicons-no-alt"}),
    ])
  }

  $(document).ready(() => {
    document.body.appendChild(App())
    $('.wpra-footer-grid').hide()
    initTimer()
    initDismissBtn()
  })

  function initTimer() {
    setInterval(() => {
      let now = new Date()
      let diff = (EndDate.getTime() - now.getTime()) / 1000

      let days, hours, mins, secs

      if (diff <= 0) {
        days = hours = mins = secs = 0
      } else {
        days = Math.floor(diff / 86400)
        diff -= (days * 86400)

        hours = Math.floor(diff / 3600) % 24
        diff -= (hours * 3600)

        mins = Math.floor(diff / 60) % 60
        diff -= (mins * 60)

        secs = Math.max(0, Math.floor(diff) % 60)
        mins = Math.max(0, mins)
        hours = Math.max(0, hours)
        days = Math.max(0, days)
      }

      $(".wpra-bf23__timer__num[data-unit=\"days\"]").text(days.toString().padStart(2, "0"))
      $(".wpra-bf23__timer__num[data-unit=\"hours\"]").text(hours.toString().padStart(2, "0"))
      $(".wpra-bf23__timer__num[data-unit=\"mins\"]").text(mins.toString().padStart(2, "0"))
      $(".wpra-bf23__timer__num[data-unit=\"secs\"]").text(secs.toString().padStart(2, "0"))
    }, 1000)
  }

  function initDismissBtn() {
    $(".wpra-bf23__dismiss").click(() => dismiss())
  }

  function dismiss() {
    $(".wpra-bf23").remove()
    $('.wpra-footer-grid').show()
    $.ajax({
      url: Config.ajaxUrl,
      method: "POST",
      data: {
        action: Config.action,
        _wpnonce: Config.nonce,
      },
      error: (xhr, status, error) => {
        console.error("Failed to dismiss WPRA BF23 banner", xhr, status, error)
      },
    })
  }

  function h(tag, attrs, children) {
    const el = document.createElement(tag)

    for (let k in attrs) {
      el.setAttribute(k, attrs[k])
    }

    if (children) {
      for (let i = 0; i < children.length; i++) {
        const child = (typeof children[i] === 'string')
          ? t(children[i])
          : children[i]

        el.appendChild(child)
      }
    }

    return el
  }

  function t(content) {
    return document.createTextNode(content)
  }

})(jQuery, BF23)
