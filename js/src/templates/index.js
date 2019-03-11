// require('./../../../css/src/plugins/index.scss')
import * as UiFramework from '@rebelcode/ui-framework'
import Bottle from 'bottlejs'
import TemplatesApplication  from './app'
import Vue from 'vue'

console.info({UiFramework})

const { Container, Core, Services } = UiFramework

/*
 * Extend UI framework object.
 */
if (window.UiFramework) {
  window.UiFramework = Object.assign({}, window.UiFramework, Core.UiFramework)
}

console.info({ Container, Core, Services }, Services.HookService)

let services = {
  uiFramework: UiFramework,
  hooks: new Services.HookService,
  document: document,
  vue: function (container) {
    let VueC = Vue.extend()
    VueC.use(container.uiFramework.Core.InjectedComponents, {
      container
    })
    VueC.version = Vue.version
    VueC.config = Vue.config
    return VueC
  }
}
const containerFactory = new Container.ContainerFactory(Bottle)
const app = new Core.UiFramework.App(containerFactory, services)

window.UiFramework.registerPlugin('templates-app', TemplatesApplication)

app.use([
  'templates-app',
  // 'test-plugin'
])
app.init({
  '#wpra-templates-app': 'App',
})
