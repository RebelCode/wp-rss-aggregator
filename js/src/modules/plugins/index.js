require('css/src/plugins/index.scss')

import Vue from 'vue'
import PluginDisablePoll from './PluginDisablePoll'

new Vue({
  el: '#wpra-plugins-app',
  template: '<PluginDisablePoll/>',
  components: { PluginDisablePoll }
})
