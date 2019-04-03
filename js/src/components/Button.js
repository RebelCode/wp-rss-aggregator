export default {
  props: {
    loading: {
      type: Boolean,
      default: false,
    }
  },
  render () {
    return <button
      disabled={this.loading}
      class={{'button': true, 'loading-button': this.loading}}
    >{ this.$slots.default }</button>
  }
}
