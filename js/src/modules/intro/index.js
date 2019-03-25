require('css/src/intro/steps.scss')

import Vue from 'vue'
import Wizard from './Wizard'
import 'whatwg-fetch'

new Vue({
  el: '#wpra-wizard-app',
  template: '<Wizard/>',
  components: { Wizard }
})
