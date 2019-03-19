import axios from 'axios'
import Vuex from 'vuex'
import List from './List'
import Edit from './Edit'

import makeRouterApp from './RouterApp.js'
import Router from './Router'
import templates from './store'

/**
 * Main application's container.
 */
export default {
  register (services) {
    /*
     * Application router instance.
     */
    services['router'] = () => {
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

    /*
     * Application with client side routes.
     */
    services['App'] = (container) => {
      return makeRouterApp(container)
    }

    /*
     * Setup and register central storage management.
     */
    services['vuex'] = ({ vue }) => {
      vue.use(Vuex)
      return Vuex
    }

    services['store'] = ({ vuex }) => {
      return new vuex.Store({
        modules: {
          templates
        },
        state: {}
      })
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
