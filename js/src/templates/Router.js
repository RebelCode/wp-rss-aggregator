export default class Router {
  constructor (routes) {
    this.routes = routes
  }

  setApp (app) {
    this.app = app
  }

  findRoute (location) {
    return this.routes.find(({ route }) => {
      return location.indexOf(route) !== -1
    })
  }

  buildRoute (route) {
    if (route.name) {
      let routeObject = this.routes.find(r => r.name === route.name)
      if (!routeObject) {
        return null
      }
      return routeObject.route
    }
  }

  navigate (route) {
    if (this.app) {
      this.app.currentRoute = this.buildRoute(route)
    }
    window.history.pushState(
      null,
      null,
      this.buildRoute(route)
    )
  }
}
