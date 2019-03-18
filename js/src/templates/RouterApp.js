export default function (router) {
  return {
    data () {
      return {
        params: {},
        currentRoute: null,
      }
    },
    created () {
      router.setApp(this)
      this.currentRoute = router.parseLocation(window.location)
    },
    mounted () {
      window.addEventListener('popstate', () => {
        this.currentRoute = router.parseLocation(window.location)
      })
    },
    methods: {
      ViewComponent () {
        const matchingView = router.findRoute(this.currentRoute)
        return matchingView.component
      }
    },
    render (h) {
      return h(this.ViewComponent())
    }
  }
}
