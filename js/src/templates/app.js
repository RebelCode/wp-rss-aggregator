import App from './App.vue'
import AppPage from './AppPage.vue'

/**
 * Main application's container.
 */
export default {
  register (services) {
    console.info('registering services', App)
    services['App'] = AppPage
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

    container.hooks.register('postbox-content-template-details', function (vnode) {
      console.info('postbox-content-template-details', {vnode, 'this': this})
      vnode.push(this.$createElement('div', ['Hello from hook: ' + this.expanded]))
      return vnode
    })
    // Application run logic.
  },
}
