export default {
  props: {
    path: {},
    gate: {}
  },
  inject: [
    'router'
  ],
  methods: {
    getPath () {
      return this.router.buildRoute(this.path)
    },
    navigate (e) {
      const allowed = !this.gate || this.gate()

      e.preventDefault()

      if (allowed) {
        this.router.navigate(this.path)
      }
    }
  },
  render () {
    const path = this.getPath()
    return <a href={path} onClick={this.navigate}>{ this.$slots.default }</a>
  }
}
