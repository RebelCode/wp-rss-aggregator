import axios from 'axios'

import App from './App.vue'
import AppPage from './AppPage.vue'

/**
 * Main application's container.
 */
export default {
  register (services) {
    services['App'] = App

    services['http'] = () => {
      /*
       * Create authorized client for requests when nonce
       * exists in global WPRA variable.
       */
      console.info({WpraGlobal})
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
