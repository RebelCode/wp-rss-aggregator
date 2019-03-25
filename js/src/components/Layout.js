export default {
  render () {
    return (
      <div id="post-body">
        {
          this.$slots.default
        }
      </div>
    )
  }
}
