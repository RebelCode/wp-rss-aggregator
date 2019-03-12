import App from './App.vue'

/**
 * Main application's container.
 */
export default {
  register (services) {
    console.info('registering services', App)
    services['App'] = App
    return services
  },
  run ({ container }) {
    console.info(container.vue)

    container.hooks.register('wpra-templates-list-cells', function (cells) {
      cells['id'] = ({ row }) => {
        return this.$createElement('div', ['ID: ' + row.id])
      }
      return cells
    })

    container.hooks.register('wpra-templates-list', function (vnode) {
      vnode.children.push(this.$createElement('div', ['From hook.']))
      console.info({vnode, 'this': this})
      return vnode
    })
    // Application run logic.
  },
}
