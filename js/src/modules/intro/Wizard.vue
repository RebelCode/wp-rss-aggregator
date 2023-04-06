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
                            <a :href="validateLink" target="_blank" v-if="isFeedError">Validate feed</a>
                        </div>
                    </form>

                    <div class="wizard_error" v-if="isFeedError">
                        <p>This RSS feed URL appears to be invalid. Here are a couple of things you can try:</p>
                        <ol>
                            <li>Check whether the URL you entered is the correct one by trying one of the options when clicking on "How do I find an RSS feed URL?" below.</li>
                            <li>
                                Test out this other RSS feed URL to make sure the plugin is working correctly - https://www.wpmayor.com/feed/ - If it works, you may <a :href="supportUrl" target="_blank">contact us here</a> to help you with your source.
                            </li>
                            <li>Test the URL's validity by W3C standards, the standards we use in our plugins, using the “Validate feed” link above.</li>
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
                        <p><a :href="knowledgeBaseUrl" target="_blank">Browse our Knowledge Base for more information.</a></p>
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
                                <template v-if="item.date || item.author">
                                    <template v-if="item.date">
                                        Published on {{ item.date }}
                                    </template>
                                    <template v-if="item.date && item.author">|</template>
                                    <template v-if="item.author">
                                        By {{ item.author }}
                                    </template>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="wrpa-shortcode">
                        <div class="wrpa-shortcode-preview">
                            <div class="wrpa-shortcode-label">
                                Create a draft page to preview these feed items on your site:
                            </div>
                            <a :href="previewUrl" target="_blank" class="button"
                               @click="preparePreview"
                               :class="{'button-primary': isPrepared, 'loading-button': isPreparing}"
                            >
                                {{ isPrepared ? 'Preview the Page' : 'Create Draft Page' }}
                            </a>
                        </div>
                        <div class="wrpa-shortcode-form" @click="copyToClipboard()">
                            <div class="wrpa-shortcode-label">
                                Copy the shortcode to any page or post on your site:
                            </div>
                            <input class="wrpa-shortcode-form__shortcode"
                                   type="text"
                                   readonly
                                   value="[wp-rss-aggregator]"
                                   ref="selected"
                            />
                            <div class="wrpa-shortcode-form__button">
                                {{ isCopied ? 'Copied!' : 'Click to copy' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wizard_content" :key="activeScreen" v-if="active('finish')" style="max-width: unset">
                    <div class="wizard_hello">
                        That's it! Your first feed source is ready to go.
                    </div>

                    <p>
                        Click the button below to continue to WP RSS Aggregator.
                    </p>

                    <div class="wpra-sign-up" v-if="signup.enabled">
                        <div class="wpra-sign-up-form" v-if="!signup.didSignUp">
                            <div class="wpra-sign-up-row">
                                <div class="wpra-sign-up-leading-text">
                                  Boost your website's content game with our exclusive, FREE guide revealing 5 expert
                                  tips on aggregation and curation – simply subscribe to our newsletter and elevate your
                                  site's impact now!
                                </div>
                            </div>
                            <div class="wpra-sign-up-row">
                                <input
                                    type="text"
                                    v-model="signup.userName"
                                    placeholder="Your name"
                                />
                            </div>
                            <div class="wpra-sign-up-row">
                                <input
                                    type="text"
                                    v-model="signup.userEmail"
                                    placeholder="Your e-mail"
                                    @keydown.enter="signUp"
                                />
                            </div>
                            <button class="wpra-blue-button wpra-blue-button-small"
                                    :class="{'wpra-blue-button-loading': signup.loading}"
                                    @click="signUp">
                                <span>Unlock My Free Guide Now!</span>
                                <ArrowCaretRight />
                            </button>
                            <div class="wpra-sign-up-row">
                                <div class="wpra-sign-up-notice-text">
                                  By unlocking the FREE guide, you'll join our list for newsletters and updates. We
                                  respect your privacy and won't share your information with any third-parties. You can
                                  opt out at any time. Clicking the button above confirms your agreement to these terms.
                                </div>
                            </div>
                            <div v-if="signup.error" class="wpra-sign-up-error">
                                {{signup.error}}
                            </div>
                        </div>

                        <div class="wpra-sign-up-done" v-if="signup.didSignUp">
                            Thank you for subscribing! Your FREE expert guide to content aggregation and curation is on
                            its way to your inbox. Get ready to boost your website's content game and make a lasting
                            impact!
                        </div>

                        <div class="wpra-sign-up-upgrade">
                            <div class="wpra-sign-up-upgrade-boob">
                                <svg viewBox="0 0 100 50">
                                    <path d="M 0 50 A 50 50 0 0 1 100 50" />
                                </svg>
                                <img :src="wpraIconUrl" alt="WP RSS Aggregator logo" />
                            </div>

                            <div class="col">
                                <div class="wpra-sign-up-upgrade-heading">Do more with your content</div>
                                <p>
                                    In 2019, Erik rapidly grew Personal Finance Blogs by curating content from the
                                    personal finance space.
                                </p>
                                <p>
                                    The
                                    <a href="https://www.wprssaggregator.com/upgrade/" target="_blank">
                                        WP RSS Aggregator Pro Plan
                                    </a>
                                    provided the flexibility he needed, together with powerful keyword filtering to
                                    control his quality content.
                                </p>
                                <a href="https://www.wprssaggregator.com/upgrade/" class="wpra-blue-button wpra-blue-button-large" target="_blank">
                                    <span>Get WP RSS Aggregator Pro</span>
                                    <ArrowCaretRight />
                                </a>
                                <div class="wpra-feedback">
                                    <div class="wpra-feedback__rating">
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                    </div>
                                    <div class="wpra-feedback__by">
                                        <a :href="caseStudyUrl" target="_blank">
                                            400+ 5-star reviews
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <div class="connect-actions pad">
                <div class="pad-item--grow">
                    <button v-if="!active('finish')"
                            class="button-clear"
                            @click="finish"
                    >
                        Skip the introduction
                    </button>
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
                            :class="{
                                'loading-button': isLoading,
                                'button-primary': !active('finish'),
                                'button-secondary': active('finish')
                            }"
                    >
                        {{
                          active('finish')
                            ? (signup.didSignUp || !signup.enabled
                              // The last step after sign up or for pro users
                              ? 'Continue to the plugin'
                              // The last step during sign up
                              : 'Skip and continue to the plugin')
                            // All steps except the last one
                            : 'Next'
                        }}
                        <ArrowCaretRight />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import Expander from 'app/components/Expander'
  import { post } from 'app/utils/fetch'
  import { copyToClipboard } from 'app/utils/copy'
  import ArrowCaretRight from "../../components/ArrowCaretRight.vue"

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
          title: _('All done!'),
          description: false,
          next: this.completeIntroduction,
          completed: () => {
            return this.feed.items.length && this.itemsPassed
          }
        }],
        isCopied: false,

        isPreparing: false,
        isPrepared: false,

        transition: 'slide-up', // 'slide-down',

        activeScreen: 'finish',
        form: {
          feedSourceUrl: null,
        },
        signup: {
          enabled: CONFIG.userIsFree,
          userName: CONFIG.userName,
          userEmail: CONFIG.userEmail,
          didSignUp: false,
          loading: false,
          error: "",
        },
        itemsPassed: false,

        stepLoading: false,
        isLoading: false,

        isFeedError: false,

        feed: {
          items: [],
        },
        previewUrl: CONFIG.previewUrl,
        proPlanUrl: CONFIG.proPlanUrl,
        proPlanCtaUrl: CONFIG.proPlanCtaUrl,
        addOnsUrl: CONFIG.addOnsUrl,
        supportUrl: CONFIG.supportUrl,
        demoImageUrl: CONFIG.demoImageUrl,
        caseStudyUrl: CONFIG.caseStudyUrl,
        knowledgeBaseUrl: CONFIG.knowledgeBaseUrl,
        wpraIconUrl: CONFIG.wpraIconUrl,
      }
    },
    computed: {
      validateLink () {
        return 'https://validator.w3.org/feed/check.cgi?url=' + encodeURI(this.form.feedSourceUrl)
      },

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
      preparePreview (e) {
        if (this.isPreparing) {
          e.preventDefault()
          return
        }
        if (!this.isPrepared) {
          e.preventDefault()
          this.isPreparing = true
          fetch(this.previewUrl).then(() => {
            this.isPreparing = false
            this.isPrepared = true
          })
        }
      },

      signUp() {
        const data = Object.assign(CONFIG.signupEndpoint.defaultPayload, {
          name: this.signup.userName,
          email: this.signup.userEmail,
        })

        this.signup.loading = true;
        this.signup.error = ""

        return post(CONFIG.signupEndpoint.url, data)
          .then(response => {
            console.log("Response:", response)
            this.signup.didSignUp = true;
            this.signup.loading = false;
            this.signup.error = ""
          })
          .catch(response => {
            this.signup.loading = false;
            this.signup.error = response.error;
          })
      },

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
        if (confirmFinish) {
          if (confirm('Are you sure you want to skip the introduction?')) {
            visitList()
          }
          return
        }
        visitList()
        // redirect to the URL.
      },

      active (pageName) {
        return this.activeScreen === pageName
      },

      copyToClipboard () {
        if (this.isCopied) {
          return
        }
        copyToClipboard('[wp-rss-aggregator]')
        this.isCopied = true
        setTimeout(() => {
          this.isCopied = false
        }, 1000)
      }
    },
    components: {
        ArrowCaretRight,
      Expander
    }
  }
</script>
