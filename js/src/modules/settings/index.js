import Vue from 'vue'
import NoticeBlock from 'app/components/NoticeBlock'

new Vue({
  el: '#wpra-settings-app',
  render (h) {
    return h(NoticeBlock, {
      props: WpraSettings.notice
    })
  }
})
