import Button from './Button'

/**
 * Big notice block that is used to provide some information for users.
 * Also can display "learn more" button to allow users to read more about
 * the announce.
 */
export default {
  data () {
    return {
      shouldBeVisible: true,
    }
  },

  props: {
    /**
     * Unique identifier of the notice block.
     *
     * @property {string}
     */
    id: {
      type: String,
      required: true,
    },

    /**
     * Visible title on top of the block.
     *
     * @property {string}
     */
    title: {
      type: String,
    },

    /**
     * The notice's block body. Can be HTML with text formatting, links and so on.
     *
     * @property {string}
     */
    body: {
      type: String,
    },

    /**
     * The link to a "learn more" article. Will be opened in a new tab.
     * If value is empty, "learn more" button won't be rendered.
     *
     * @property {string|boolean}
     */
    learnMore: {
      default: false,
    },

    /**
     * Text on the button that will hide the message.
     *
     * @property {string}
     */
    okayText: {
      type: String,
      default: 'Got it'
    },

    /**
     * Text on the button that will open a "learn more" article.
     *
     * @property {string}
     */
    learnMoreText: {
      type: String,
      default: 'Learn more'
    },

    /**
     * Whether the notice block is visible in UI.
     *
     * @property {boolean}
     */
    visible: {
      type: Boolean,
      default: true,
    },
  },

  computed: {
    isVisible () {
      return this.visible
        && this.shouldBeVisible
        && JSON.parse(localStorage.getItem(this.getBlockKey()) || 'true')
    }
  },

  methods: {
    /**
     * Hide the block.
     */
    onOkayClick () {
      this.shouldBeVisible = false
      localStorage.setItem(this.getBlockKey(), JSON.stringify(false))
    },

    /**
     * Open learn more link in a new tab when user clicks on the "learn more" button.
     */
    onLearnMoreClick () {
      window.open(this.learnMore, '_blank').focus()
    },

    /**
     * Notice's block key in local storage.
     *
     * @return {string}
     */
    getBlockKey () {
      return `wpra-${this.id}-visible`
    }
  },

  render () {
    /*
     * Template is not rendered when it was hidden by user, or server.
     */
    if (!this.isVisible) {
      return null
    }

    let learnMoreButton = this.learnMore ?
      <Button class="button-clear" nativeOnClick={this.onLearnMoreClick}>
        {this.learnMoreText} <span class="dashicons dashicons-external"/>
      </Button> : null

    return (
      <div class="wpra-notice-block">
        <div class="wpra-notice-block__title">{this.title}</div>
        <div class="wpra-notice-block__body" {...{domProps: {innerHTML: this.body}}}/>
        <div class="wpra-notice-block__buttons">
          <Button class="brand button-primary" nativeOnClick={this.onOkayClick}>{this.okayText}</Button>
          {learnMoreButton}
        </div>
      </div>
    )
  }
}
