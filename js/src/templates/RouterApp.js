export default function (router) {
  return {
    data () {
      return {
        currentRoute: this.getRouteFromLocation(window.location)
      }
    },
    created () {
      router.setApp(this)
    },
    mounted () {
      window.addEventListener('popstate', () => {
        this.currentRoute = this.getRouteFromLocation(window.location)
      })
    },
    methods: {
      getRouteFromLocation (location) {
        return location.pathname + location.search
      },
    },
    computed: {
      ViewComponent () {
        const matchingView = router.findRoute(this.currentRoute)
        return matchingView.component
      }
    },
    render (h) {
      return h(this.ViewComponent)
    }
  }
}
