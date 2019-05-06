require('css/src/templates/index.scss')

import * as UiFramework from '@rebelcode/ui-framework'
import Bottle from 'bottlejs'
import TemplatesApplication  from './app'
import Vue from 'vue'

const { Container, Core, Services } = UiFramework

/*
 * Extend UI framework object.
 */
if (window.UiFramework) {
  window.UiFramework = Object.assign({}, window.UiFramework, Core.UiFramework)
}

let services = {
  uiFramework: UiFramework,
  hooks: new Services.HookService,
  document: document,
  vue: function (container) {
    Vue.use(container.uiFramework.Core.InjectedComponents, {
      container
    })
    return Vue
  }
}
const containerFactory = new Container.ContainerFactory(Bottle)
const app = new Core.UiFramework.App(containerFactory, services)

window.UiFramework.registerPlugin('templates-app', TemplatesApplication)

app.use(WpraTemplates.modules || ['templates-app'])
app.init({
  '#wpra-templates-app': 'App',
})
