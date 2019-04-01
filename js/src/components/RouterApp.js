export default function ({ store, router }) {
  return {
    store,
    data () {
      return {
        afterNavigate: () => {},
        params: {},
        currentRoute: null,
      }
    },
    created () {
      router.setApp(this)
      this.currentRoute = router.parseLocation(window.location)
      this.navigated()
    },
    mounted () {
      window.addEventListener('popstate', () => {
        this.currentRoute = router.parseLocation(window.location)
        this.navigated()
      })
    },
    methods: {
      ViewComponent () {
        const matchingView = router.findRoute(this.currentRoute)
        return matchingView.component
      },
      navigated () {
        this.$nextTick(() => {
          const main = this.$refs.main
          if (!main || !main.navigated) {
            return
          }
          main.navigated({
            route: router.findRoute(this.currentRoute),
          })
        })
      }
    },
    render (h) {
      const content = h(this.ViewComponent(), {
        ref: 'main'
      })
      this.afterNavigate()
      return content
    }
  }
}
