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
    },
    mounted () {
      console.info('router application', this, this.$store)
      window.addEventListener('popstate', () => {
        this.currentRoute = router.parseLocation(window.location)
        router.onNavigate({
          params: router.params
        })
      })
    },
    methods: {
      ViewComponent () {
        const matchingView = router.findRoute(this.currentRoute)
        return matchingView.component
      },
    },
    render (h) {
      const content = h(this.ViewComponent())
      this.afterNavigate()
      return content
    }
  }
}
