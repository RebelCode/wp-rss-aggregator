<template>
    <div class="wizard-holder animated fadeIn">
        <div class="connect-steps">
            <div class="step-items">
                <div class="step-progress" :class="'step-progress--' + activeScreenIndex"></div>
                <div class="step-item"
                     :class="{ 'step-item_active': active(screen.id), 'step-item_completed' : screen.completed() && index < activeScreenIndex }"
                     v-for="(screen, index) of screens"
                >
                    <div class="step-item__status">
                        <span class="dashicons dashicons-yes" v-if="screen.completed() && index < activeScreenIndex"></span>
                    </div>
                    <div class="step-item__info">
                        <div class="step-item__title">{{ screen.title }}</div>
                        <div class="step-item__description" v-if="screen.description">{{ screen.description }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="wizard">
            <transition :name="transition" mode="out-in">
                <div class="wizard_content" :key="activeScreen" v-if="active('feed')">
                    <div class="wizard_hello">
                        Enter your first RSS Feed URL
                    </div>

                    <form id="feedForm" @submit.prevent="next" class="wizard_info">
                        <div class="form-group">
                            <input type="text" placeholder="https://www.sourcedomain.com/feed/" v-model="form.feedSourceUrl"
                                   class="wpra-feed-input"
                            >
                            <span class="dashicons dashicons-warning warning-icon" v-if="isFeedError"></span>
                        </div>
                    </form>

                    <div class="wizard_error" v-if="isFeedError">
                        <p>Oh no! This RSS feed URL appears to be invalid. Here are a couple of things you can try:</p>
                        <ol>
                            <li>Check whether the URL you entered is the correct one by trying one of the options when clicking on "How do I find an RSS feed URL?" below.</li>
                            <li>Test out this other RSS feed URL to make sure the plugin is working correctly. If it works, you may contact us here so we can help you figure out what's wrong with your source: https://www.wpmayor.com/feed/</li>
                            <li>Test the URL's validity by W3C standards, the standards we use in our plugins: https://validator.w3.org/feed/</li>
                        </ol>
                    </div>

                    <expander title="How do I find an RSS feed URL?">
                        <p>WP RSS Aggregator fetches feed items through RSS feeds. Almost every website in the world provides an RSS feed. Here's how to find it:</p>
                        <p>Option 1: Add /feed to the website's homepage URL </p>
                        <p>Many sites have their RSS feed at the same URL. For instance, if the website's URL is www.thiswebsite.com, then the RSS feed could be at www.thiswebsite.com/feed.</p>
                        <p>Option 2: Look for the RSS share icon</p>
                        <p>Many websites have share icons on their pages for Facebook, Twitter and more. Many times, there will also be an orange RSS icon. Click on that to access the RSS feed URL.</p>
                        <p>Option 3: Browser RSS Auto-Discovery</p>
                        <p>Most browsers either include an RSS auto-discovery tool by default or they allow you to add extensions for it. Firefox shows an RSS icon above the website, in the address bar, which you can click on directly. Chrome offers extensions such as this one.</p>
                        <p>Option 4: Look at the Page Source</p>
                        <p>When on any page of the website you're looking to import feed items from, right click and press "View Page Source". Once the new window opens, use the “Find” feature (Ctrl-F on PC, Command-F on Mac) and search for " RSS". This should take you to a line that reads like this (or similar):</p>
                        <p>
                            <code>
                                &#x3C;link rel=&#x22;alternate&#x22; type=&#x22;application/rss+xml&#x22; title=&#x22;RSS Feed&#x22; href=&#x22;https://www.sourcedomain.com/feed/&#x22; /&#x3E;
                            </code>
                        </p>
                        <p>The RSS feed’s URL is found between the quotes after href=. In the above case, it would be  https://www.sourcedomain.com/feed/.</p>
                    </expander>
                </div>

                <div class="wizard_content" :key="activeScreen" v-if="active('feedItems')">
                    <div class="wizard_hello">
                        Latest feed items from your selected feed source:
                    </div>

                    <div class="wpra-feed-items">
                        <div class="wpra-feed-item" v-for="item of feed.items">
                            <div class="wpra-feed-item__link">
                                <a :href="item.permalink" target="_blank">{{ item.title }}</a>
                            </div>
                            <div class="wpra-feed-item__info">
                                Published on {{ item.date }} | By {{ item.author }}
                            </div>
                        </div>
                    </div>

                    <div class="wizard_label">
                        Copy and paste this shortcode to any page or post to display the full list:
                    </div>

                    <div class="wrpa-shortcode">
                        <div class="wrpa-shortcode-form" @click="copyToClipboard('[wp-rss-aggregator]')">
                            <div class="wrpa-shortcode-form__shortcode">
                                [wp-rss-aggregator]
                            </div>
                            <div class="wrpa-shortcode-form__button">
                                Click to copy
                            </div>
                        </div>
                        OR
                        <a :href="previewUrl" target="_blank">Preview the feed items on a new page</a>
                    </div>
                </div>

                <div class="wizard_content" :key="activeScreen" v-if="active('finish')">
                    <div class="wizard_hello">
                        You're on your way!
                    </div>

                    <div class="wpra-cols-title">
                        Do more with WP RSS Aggregator. Here's a look at what CryptoHeadlines.com is doing:
                    </div>

                    <div class="wpra-cols">
                        <div class="col">
                            <p>CryptoHeadlines.com displays latest news, Youtube videos, podcasts, jobs and more from the Cryptocurrency industry.</p>
                            <p>It uses Feed to Post to import articles, Youtube videos, and podcast links.</p>
                            <p>Full Text RSS Feeds is used to fetch the full content of the job listings to present more information to the potential applicant.</p>
                            <p>Keyword Filtering is used to filter out content that contains profanity and keywords or phrases deemed as inappropriate.</p>
                            <div style="margin-bottom: .5rem">
                                <a :href="addOnsUrl" class="button button-primary" target="_blank">Browse Add-ons</a>
                            </div>
                            <div>
                                <a :href="supportUrl" target="_blank">Contact support for more information.</a>
                            </div>
                        </div>
                        <div class="col">
                            <img src="https://www.wprssaggregator.com/wp-content/uploads/2012/09/travel-blogger-community400px@2x.png"
                                 class="img wpra-demo-photo">

                            <div class="wpra-feedback">
                                <div class="wpra-feedback__photo">
                                    <img src="https://www.wprssaggregator.com/wp-content/themes/wp_rss_theme/assets/images/review2.jpg">
                                </div>
                                <div class="wpra-feedback__copy">
                                    <div class="wpra-feedback__text">
                                        I’ve been testing several RSS plug-ins and some worked for a while and then stopped. RSS Aggregator is very stable.
                                    </div>
                                    <div class="wpra-feedback__rating">
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                    </div>
                                    <div class="wpra-feedback__by">
                                        Review by abkuijer
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <div class="connect-actions pad">
                <div class="pad-item--grow">
                    <transition name="fade" mode="out-in">
                        <div v-if="active('finish')" key="info" class="wpra-success">
                            🎉
                            Congratulations! You've succesfully set up your first feed source. Continue adding more sources and setting up your preferred options in our Settings.
                        </div>
                        <button v-else class="button-clear"
                                key="button"
                                @click="finish"
                        >
                            Skip the introduction
                        </button>
                    </transition>
                    &nbsp;
                </div>
                <div class="pad-item--no-shrink">
                    <button class="button-clear"
                            @click="back"
                            v-if="isBackAvailable"
                    >
                        Back
                    </button>
                    <button @click="next"
                            class="button button-large"
                            :class="{'loading-button': isLoading, 'button-primary': !active('finish')}"
                    >
                        {{ active('finish') ? 'Continue' : 'Next' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import Expander from './Expander'
  import { post } from './fetch'

  const _ = (str) => str

  const CONFIG = window.wprssWizardConfig

  export default {
    data () {
      return {
        prevHeight: 0,
        screens: [{
          id: 'feed',
          title: _('Add feed source URL'),
          description: false,
          next: this.submitFeed,
          completed: () => {
            return this.feed.items.length
          },
          entered: () => {
            this.focusOnInput('feed')
          }
        }, {
          id: 'feedItems',
          title: _('Display feed items'),
          description: false,
          next: this.continueItems,
          completed: () => {
            return this.feed.items.length && this.itemsPassed
          }
        }, {
          id: 'finish',
          title: _('Complete introduction'),
          description: false,
          next: this.completeIntroduction,
          completed: () => {
            return this.feed.items.length && this.itemsPassed
          }
        }],
        transition: 'slide-up', // 'slide-down',

        activeScreen: 'feed',
        form: {
          feedSourceUrl: null,
        },
        itemsPassed: false,

        stepLoading: false,
        isLoading: false,

        isFeedError: false,

        feed: {
          items: [],
        },
        previewUrl: CONFIG.previewUrl,
        addOnsUrl: CONFIG.addOnsUrl,
        supportUrl: CONFIG.supportUrl
      }
    },
    computed: {
      activeScreenIndex () {
        return this.screens.findIndex(screen => screen.id === this.activeScreen)
      },
      currentScreen () {
        return this.screens.find(screen => screen.id === this.activeScreen)
      },
      actionCompleted () {
        return this.currentScreen.completed()
      },
      isBackAvailable () {
        return this.activeScreenIndex > 0 && this.activeScreenIndex < this.screens.length
      },
    },
    mounted () {
      this.onScreenEnter()
    },
    methods: {
      /**
       * Submits first feed step.
       *
       * @return {Promise<any>}
       */
      submitFeed () {
        const data = Object.assign(CONFIG.feedEndpoint.defaultPayload, {
          wprss_intro_feed_url: this.form.feedSourceUrl
        })
        this.isLoading = true
        this.isFeedError = false
        return post(CONFIG.feedEndpoint.url, data).then((responseData) => {
          this.feed.items = responseData.data.feed_items.slice(0, 3)
          this.isLoading = false
          return {}
        }).catch((resp) => {
          this.isLoading = false
          this.isFeedError = true
          throw resp
        })
      },

      /**
       * Continue from items step.
       *
       * @return {Promise<any>}
       */
      continueItems () {
        this.itemsPassed = true
        return Promise.resolve({})
      },

      /**
       * Complete the introduction and proceed to sources list.
       *
       * @return {Promise<any>}
       */
      completeIntroduction () {
        return Promise.resolve({})
      },

      /**
       * Go to the next screen in this wizard.
       */
      next () {
        this.transition = 'slide-up'
        const nextTransistor = this.currentScreen.next ? this.currentScreen.next : () => Promise.resolve(false)
        this.stepLoading = true
        nextTransistor().then(result => {
          this.stepLoading = false
        }, (err) => {
          throw err
        }).then(() => {
          const nextStepIndex = this.activeScreenIndex + 1
          if (nextStepIndex >= this.screens.length) {
            this.finish()
          }
          else {
            this.activeScreen = this.screens[nextStepIndex].id
            this.onScreenEnter()
          }
        }).catch((err) => {
          console.error(err)
        })
      },

      /**
       * Run on screen event.
       */
      onScreenEnter () {
        this.$nextTick(() => {
          if (this.currentScreen.entered) {
            this.currentScreen.entered()
          }
        })
      },

      /**
       * Focus on some ref input.
       */
      focusOnInput (refName) {
        if (!this.$refs[refName] || !this.$refs[refName].focus) {
          return false
        }
        this.$refs[refName].focus()
      },

      /**
       * Go back in the wizard on one step.
       */
      back () {
        this.transition = 'slide-down'
        this.activeScreen = this.screens[this.activeScreenIndex - 1].id
      },

      /**
       * Finish this wizard.
       */
      finish (confirmFinish = false) {
        const visitList = () => window.location.href = CONFIG.feedListUrl
        if (confirmFinish && confirm('Are you sure you want to skip the introduction?')) {
          visitList()
        }
        visitList()
        // redirect to the URL.
      },

      active (pageName) {
        return this.activeScreen === pageName
      },

      copyToClipboard (text) {
        window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
      }
    },
    components: {
      Expander
    }
  }
</script>
