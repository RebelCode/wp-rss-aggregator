const getJsonFromUrl = (url) => {
  if (!url) url = location.href
  var question = url.indexOf('?')
  var hash = url.indexOf('#')
  if (hash == -1 && question == -1) return {}
  if (hash == -1) hash = url.length
  var query = question == -1 || hash == question + 1 ? url.substring(hash) :
    url.substring(question + 1, hash)
  var result = {}
  query.split('&').forEach(function (part) {
    if (!part) return
    part = part.split('+').join(' ') // replace every + with space, regexp-free version
    var eq = part.indexOf('=')
    var key = eq > -1 ? part.substr(0, eq) : part
    var val = eq > -1 ? decodeURIComponent(part.substr(eq + 1)) : ''
    var from = key.indexOf('[')
    if (from == -1) result[decodeURIComponent(key)] = val
    else {
      var to = key.indexOf(']', from)
      var index = decodeURIComponent(key.substring(from + 1, to))
      key = decodeURIComponent(key.substring(0, from))
      if (!result[key]) result[key] = []
      if (!index) result[key].push(val)
      else result[key][index] = val
    }
  })
  return result
}

export default class Router {
  constructor (routes, options) {
    this.routes = routes
    this.options = options
    this.baseParams = options.baseParams || ['post_type', 'page', 'action', 'id']
  }

  get params () {
    return this.app ? this.app.params : {}
  }

  setApp (app) {
    this.app = app
    this.app.afterNavigate = this.options.afterNavigating || (() => {})
  }

  findRoute (location) {
    return this.routes.find(({route}) => {
      return location.indexOf(route) !== -1
    })
  }

  updateParams (params) {
    this.app.$set(this.app, 'params', params)
  }

  mergeParams (paramsPart) {
    let currentParams = Object.keys(this.params).filter(key => {
      return this.baseParams.indexOf(key) !== -1 || paramsPart.hasOwnProperty(key)
    }).reduce((acc, key) => {
      acc[key] = this.params[key]
      return acc
    }, {})

    let params = Object.assign({}, currentParams, paramsPart)

    this.updateParams(params)

    window.history.pushState(
      null,
      null,
      this.routeFromParams()
    )

    this.app.navigated()
  }

  routeFromParams () {
    const hasParams = !!Object.keys(this.params).length
    return location.pathname + (hasParams ? '?' + this.buildParams(this.params) : '')
  }

  buildRoute (route) {
    if (route.name) {
      let routeObject = this.routes.find(r => r.name === route.name)
      if (!routeObject) {
        return null
      }
      const routeStr = routeObject.route
      const join = routeStr.indexOf('?') !== -1 ? '&' : '?'

      return routeStr + (route.params ? join + this.buildParams(route.params ? route.params : {}) : '')
    }
  }

  buildParams (params) {
    return Object.keys(params).map(param => {
      return `${param}=${params[param]}`
    }).join('&')
  }

  parseLocation (location) {
    this.updateParams(getJsonFromUrl(location.search))
    console.info('ROUTE PARSE LOCATION PARAMS', getJsonFromUrl(location.search))
    return location.pathname + location.search
  }

  navigate (route) {
    if (this.app) {
      this.app.currentRoute = this.buildRoute(route)
    }

    this.updateParams(Object.assign({}, route.params || {}, getJsonFromUrl(this.buildRoute(route))))

    window.history.pushState(
      null,
      null,
      this.buildRoute(route)
    )

    this.app.navigated()
  }
}
