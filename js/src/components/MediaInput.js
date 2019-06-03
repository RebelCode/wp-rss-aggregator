let imageFrame = null

/**
 * @see https://core.trac.wordpress.org/browser/tags/5.1.1/src/js/_enqueues/wp/media?order=name
 *
 * @var {{media: Function}} wp
 *
 * @property {{}} value
 */
export default {
  props: {
    mediaType: {
      type: String,
      default: 'image'
    },
    mediaTitle: {
      type: String,
      default: 'Select Media'
    },
    /**
     * Property to use as a value for the media. Can be `id` or `url`.
     *
     * @property {string} mediaValueProperty
     */
    mediaValueProperty: {
      type: String,
      default: 'id',
    },
  },
  methods: {
    mediaNode () {
      this.assertMediaLoaded()

      return <div>
        <input type="text" value={this.value}/>
        <button class="button" onClick={this.openFrame}>Choose image</button>
      </div>
    },

    openFrame () {
      if (!imageFrame) {
        imageFrame = this.createFrame()
      }
      imageFrame.open()
    },

    createFrame () {
      imageFrame = wp.media({
        title: this.mediaTitle,
        multiple: false,
        library: {
          type: this.mediaType,
        }
      })

      imageFrame.on('close', () => {
        const selection = imageFrame.state().get('selection')
        let selectedAttachment = null
        selection.each((attachment) => {
          console.info({attachment})
          selectedAttachment = attachment
        })
        if (!selectedAttachment || !selectedAttachment.id) {
          return
        }
        this.$emit('input', ({
          id: selectedAttachment.id,
          url: selectedAttachment.attributes.url
        })[this.mediaValueProperty])
      })

      imageFrame.on('open', () => {
        const selection = imageFrame.state().get('selection')

        if (this.mediaValueProperty === 'id' && this.value) {
          const attachment = wp.media.attachment(this.value)
          attachment.fetch()
          selection.add(attachment ? [attachment] : [])
        }
      })

      return imageFrame
    },

    /**
     * Check whether wp.media is loaded. If not - throw an error.
     *
     * @throws Error When wp.media is not loaded.
     */
    assertMediaLoaded () {
      if (!window.wp.media) {
        throw Error('[MediaInput] wp.media dependency is not loaded')
      }
    }
  },
}
