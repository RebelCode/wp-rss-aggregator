<template>
    <div class="wpra-expander" :class="{'wpra-expander--expanded':isExpanded}">
        <div class="wpra-expander__title" @click="isExpanded = !isExpanded">
            {{ title }}
            <span class="dashicons dashicons-arrow-down-alt2"></span>
        </div>
        <transition-expand>
            <div v-if="isExpanded">
                <div class="wpra-expander__content">
                    <slot></slot>
                </div>
            </div>
        </transition-expand>
    </div>
</template>

<script>
  import TransitionExpand from './TransitionExpand'

  export default {
    data () {
      return {
        isExpanded: this.defaultExpanded
      }
    },
    props: {
      title: {},
      defaultExpanded: {
        value: false
      }
    },
    components: {
      TransitionExpand
    }
  }
</script>
