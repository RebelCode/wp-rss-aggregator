<script>
  export default {
    inject: [
      'hooks'
    ],
    mounted () {
      console.info('Welcome, Neo', this, this.hooks)
    },
    render (h) {
      let content = h('div', [
        h('div', {class: 'postbox'}, [
          h('div', {class: 'inside'}, ['App.'])
        ])
      ])
      return this.hooks.apply('wpra-templates-list', this, content)
    }
  }
</script>
