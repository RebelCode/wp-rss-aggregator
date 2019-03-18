import axios from 'axios'
import List from './List'
import Edit from './Edit'

import makeRouterApp from './RouterApp.js'
import Router from './Router'

/**
 * Main application's container.
 */
export default {
  register (services) {
    services['router'] = ({ vue }) => {
      return new Router([{
        route: WpraGlobal.templates_url_base + '&action',
        name: 'templates-form',
        component: Edit,
      }, {
        route: WpraGlobal.templates_url_base,
        name: 'templates',
        component: List,
      }])
    }

    services['App'] = ({ router }) => {
      return makeRouterApp(router)
    }

    services['http'] = () => {
      /*
       * Create authorized client for requests when nonce
       * exists in global WPRA variable.
       */
      let httpClientOptions = !!WpraGlobal && !!WpraGlobal.nonce ? {
        headers: {
          'X-WP-Nonce': WpraGlobal.nonce,
        }
      } : {}
      return axios.create(httpClientOptions)
    }

    return services
  },
  run ({ container }) {
    // run.
  },
}
