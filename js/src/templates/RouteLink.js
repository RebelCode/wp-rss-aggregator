export default {
  props: {
    path: {},
  },
  inject: [
    'router'
  ],
  methods: {
    getPath () {
      return this.router.buildRoute(this.path)
    },
    navigate (e) {
      e.preventDefault()
      this.router.navigate(this.path)
    }
  },
  render () {
    const path = this.getPath()
    return <a href={path} onClick={this.navigate}>{ this.$slots.default }</a>
  }
}
