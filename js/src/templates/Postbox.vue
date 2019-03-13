<script>
  export default {
    inject: ['hooks'],
    data () {
      return {
        expanded: true,
      }
    },
    props: {
      title: {},
      id: {}
    },
    methods: {
      toggle () {
        this.expanded = !this.expanded
      }
    },
    render () {
      return this.hooks.apply('postbox-' + this.id, this, (
        <div class="postbox">
          <button type="button" class="handlediv" aria-expanded="true" onClick={this.toggle}>
            <span class="screen-reader-text">Toggle panel: { this.title }</span>
            <span class="toggle-indicator" aria-hidden="true"></span>
          </button>
          <h2 class="hndle ui-sortable-handle"
              onClick={this.toggle}
          ><span>{ this.title }</span></h2>
          <div class="inside">
            {
              this.hooks.apply('postbox-content-' + this.id, this, [
                this.$slots.default
              ])
            }
          </div>
        </div>
      ))
    }
  }
</script>