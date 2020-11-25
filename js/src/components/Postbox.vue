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
      id: {},
      submit: {
        type: Boolean,
        default: false,
      },
      context: {},
    },
    methods: {
      toggle () {
        this.expanded = !this.expanded
      }
    },
    render (h) {
      return this.hooks.apply('postbox-' + this.id, this.context || this, (
        <div class="postbox wpra-postbox" id={ this.submit ? 'submitdiv' : ''}>
          <div class="postbox-header">
            <h2 class="hndle ui-sortable-handle" onClick={this.toggle}>
              <span>{ this.title }</span>
            </h2>
          </div>
          <div class="inside">
            {
              this.hooks.apply('postbox-content-' + this.id, this.context || this, [
                this.$slots.default
              ], {h})
            }
          </div>
        </div>
      ), {h})
    }
  }
</script>
