<template>
    <div class="wpra-plugin-disable-poll">
        <modal :active="isModalVisible"
               @close="closeModal"
               :header-class="'invisible-header'"
        >
            <div slot="header">
                <div class="wpra-plugin-disable-poll__logo">
                    <img :src="image('light-line-logo.png')" alt="">
                </div>
                <h3>
                    Do you have a moment to share why you are deactivating WP RSS Aggregator?
                </h3>
                <p>
                    Your feedback will help us to improve our plugins and service.
                </p>
            </div>

            <div slot="body">
                <SerializedForm :form="form" v-model="model"/>
            </div>

            <div slot="footer">
                <div class="footer-confirm__buttons">
                    <button class="button button-clear" @click="deactivate">
                        Skip & Deactivate
                    </button>
                    <button class="button button-primary"
                            :class="{'loading-button': isDeactivating}"
                            @click="submit">
                        Submit & Deactivate
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
  import Modal from 'app/components/Modal'
  import SerializedForm from 'app/components/SerializedForm'
  import axios from 'axios'

  /**
   * Selector string for plugin's deactivation link.
   *
   * @type {string}
   */
  const deactivateSelector = '[data-slug="wp-rss-aggregator"] .deactivate a'
  const deactivateLink = document.querySelector(deactivateSelector)

  export default {
    components: {
      Modal,
      SerializedForm,
    },
    data () {
      return {
        isDeactivating: false,
        deactivateUrl: null,
        submitUrl: WrpaDisablePoll.url,
        model: WrpaDisablePoll.model,
        form: WrpaDisablePoll.form,
        audience: WrpaDisablePoll.audience || 100,
        isModalVisible: false
      }
    },
    watch: {
      'model.reason' () {
        this.model.follow_up = null
      }
    },
    mounted () {
      const isVisible = this.getRandomInt(0, 100) < this.audience
      if (isVisible) {
        deactivateLink.addEventListener('click', this.handleDeactivateClick)
      }
    },
    methods: {
      getRandomInt (min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min + 1)) + min;
      },

      image (path) {
        return WrpaDisablePoll.image + path
      },

      handleDeactivateClick (e) {
        if (this.isModalVisible) {
          return
        }

        e.preventDefault()
        this.isModalVisible = true
      },

      closeModal () {
        this.isModalVisible = false
        this.deactivateUrl = null
      },

      submit () {
        this.isDeactivating = true
        axios.post(this.submitUrl, this.model, {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        }).then(() => {
          this.deactivate()
        }).finally(() => {
          this.isDeactivating = false
        })
      },

      deactivate () {
        deactivateLink.click()
      }
    }
  }
</script>
