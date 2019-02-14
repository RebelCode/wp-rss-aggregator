<template>
    <div class="wpra-plugin-disable-poll">
        <modal :active="isModalVisible"
               @close="isModalVisible = false"
        >
            <div slot="header">
                Quick Feedback
            </div>

            <div slot="body">
                <SerializedForm :form="form" v-model="model"/>
            </div>

            <div slot="footer">
                <div class="footer-confirm__buttons">
                    <input type="button" class="button button-clear" value="Skip & Deactivate">
                    <input type="button" class="button button-primary" value="Submit & Deactivate">
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
  import Modal from './Modal'
  import SerializedForm from './SerializedForm'

  /**
   * Selector string for plugin's deactivation link.
   *
   * @type {string}
   */
  const deactivateSelector = '[data-slug="wp-rss-aggregator"] .deactivate a'

  export default {
    components: {
      Modal,
      SerializedForm,
    },
    data () {
      return {
        model: {
          reason: null,
          follow_up: null,
          date: null
        },
        form: [
          {
            label: '',
            type: 'radio',
            name: 'reason',
            options: [
              {
                value: 'I no longer need the plugin',
              },
              {
                value: 'I found a better alternative',
              },
              {
                value: "I couldn't get the plugin to work",
              },
              {
                value: "I'm temporarily deactivating the plugin, but I'll be back",
              },
              {
                value: 'I have a WP RSS Aggregator add-on',
              },
              {
                value: 'Other',
              },
            ]
          },
          {
            label: 'Would you mind sharing its name?',
            type: 'textarea',
            name: 'follow_up',
            condition: {
              field: 'reason',
              operator: '=',
              value: 'I found a better alternative',
            },
          },
          {
            type: 'content',
            label: 'Have you <a target="_blank" href="https://wordpress.org/support/plugin/wp-rss-aggregator/">contacted our support team</a> or checked out our <a href="https://kb.wprssaggregator.com/" target="_blank">Knowledge Base</a>?',
            condition: {
              field: 'reason',
              operator: '=',
              value: 'I couldn\'t get the plugin to work',
            },
          },
          {
            type: 'content',
            label: 'This core plugin is required for all our premium add-ons. Please don\'t deactivate it if you currently have premium add-ons installed and activated.',
            condition: {
              field: 'reason',
              operator: '=',
              value: 'I have a WP RSS Aggregator add-on',
            },
          },
          {
            label: 'Please share your reason...',
            type: 'textarea',
            name: 'follow_up',
            condition: {
              field: 'reason',
              operator: '=',
              value: 'Other',
            },
          },
        ],
        isModalVisible: false
      }
    },
    mounted () {
      document.querySelector(deactivateSelector).addEventListener('click', this.handleDeactivateClick)
    },
    methods: {
      handleDeactivateClick (e) {
        e.preventDefault()

        this.isModalVisible = true
      }
    }
  }
</script>