import Postbox from 'app/components/Postbox'
import Main from 'app/components/Main'
import Sidebar from 'app/components/Sidebar'
import Layout from 'app/components/Layout'
import RouteLink from 'app/components/RouteLink'
import Input from 'app/components/Input'
import Button from 'app/components/Button'
import NoticeBlock from 'app/components/NoticeBlock'
import deepmerge from 'app/utils/deepmerge'
import DataChangesAware from 'app/mixins/DataChangesAware'
import jsonClone from 'app/utils/jsonClone'
import { copyToClipboard } from 'app/utils/copy'

export default {
  mixins: [DataChangesAware],
  data () {
    return {
      typeOptions: Object.keys(WpraTemplates.options.type)
        .filter(key => key[0] !== '_')
        .reduce((acc, key) => {
          acc[key] = WpraTemplates.options.type[key]
          return acc
        }, {}),

      model: jsonClone(WpraTemplates.model_schema),
      validation: jsonClone(WpraTemplates.model_schema),
      tooltips: jsonClone(WpraTemplates.model_tooltips),

      baseUrl: WpraTemplates.base_url,
      isSaving: false,
      isLoading: false,
    }
  },
  inject: [
    'hooks',
    'http',
    'router',
    'notification',
  ],
  mounted () {
    this.resolveEditingItem()
  },
  computed: {
    previewUrl () {
      return `${WpraGlobal.admin_base_url}?wpra_preview_template=${this.router.params.id}`
    }
  },
  methods: {
    resolveEditingItem () {
      let modelDefault = deepmerge(jsonClone(WpraTemplates.model_schema), this.$store.state.templates.preset)

      this.isLoading = true
      const loadItem = () => {
        const id = this.router.params.id
        if (!id) {
          return Promise.resolve(null)
        }
        let item = this.$store.getters['templates/item'](id)
        if (item) {
          return Promise.resolve(item)
        }
        return this.http.get(`${this.baseUrl}/${id}`).then(response => {
          return response.data
        })
      }

      loadItem().then(item => {
        this.isLoading = false
        if (!item) {
          this.$set(this, 'model', modelDefault)
          this.rememberModel()
          return
        }
        item = Object.assign({}, item)
        this.model = deepmerge(this.model, item)
        this.rememberModel()
      })
    },
    save () {
      const isNew = !this.model.id
      this.isSaving = true
      this.runRequest().then(response => {
        this.model = deepmerge(this.model, response.data)
        this.rememberModel()

        this.notification.show('Template saved!', {
          type: 'success',
          icon (el) {
            el.classList.add('dashicons', 'dashicons-yes')
            return el
          },
        })

        if (!isNew) {
          return
        }
        this.router.navigate({
          name: 'templates',
          params: {
            action: 'edit',
            id: response.data.id
          }
        })
      }, response => {
        this.notification.show('Something went wrong. Template is not saved!', {
          type: 'error',
          icon (el) {
            el.classList.add('dashicons', 'dashicons-warning')
            return el
          },
        })
      }).finally(() => {
        this.isSaving = false
      })
    },
    runRequest () {
      const method = this.model.id ? 'put' : 'post'
      const url = this.model.id ? `${this.baseUrl}/${this.model.id}` : this.baseUrl
      return this.http[method](url, this.prepareModel())
    },
    prepareModel () {
      const availableKeys = Object.keys(WpraTemplates.model_schema)

      return Object.keys(this.model)
        .filter(key => {
          /*
           * Only keys from model_schema can be saved.
           */
          if (!availableKeys.includes(key)) {
            return false
          }

          /*
           * Name and type shouldn't be passed for default template.
           */
          if (this.model.type === '__built_in' && ['name', 'type'].includes(key)) {
            return false
          }

          /*
           * Slug and ID fields are always ignored and not sent in the request body.
           */
          return !['slug', 'id'].includes(key)
        })
        .reduce((acc, key) => {
          acc[key] = this.model[key]
          return acc
        }, {})
    },
    getShortcode () {
      return `[wp-rss-aggregator template="${this.model.slug}"]`
    },
    preventLoosingNotSavedData () {
      return !this.isChanged() || confirm('You have unsaved changes. All changes will be lost if you go back to the Template list before updating. Are you sure you want to continue?')
    },
    copyShortcode (e) {
      copyToClipboard(this.getShortcode())

      const text = e.target.innerText

      e.target.style.width = e.target.getBoundingClientRect().width + 'px'
      e.target.disabled = true
      e.target.innerText = 'Copied!'

      setTimeout(() => {
        e.target.style.width = null
        e.target.innerText = text
        e.target.disabled = false
      }, 5000)
    },
  },
  render () {
    let back = {
      name: 'templates'
    }

    let minorActions = null,
      shortcode = null,
      noticeBlock = <NoticeBlock
        class={'postbox'}
        id={'templates-usage'}
        title={'Setting up your Templates'}
        body={'Templates are used to display the items imported using WP RSS Aggregator. Choose the preferred options ' +
        'below and use them anywhere on your site via our <a href="https://kb.wprssaggregator.com/article/54-displaying-imported-items-shortcode#tinymce" target="_blank">shortcode</a> ' +
        'or our <a href="https://kb.wprssaggregator.com/article/454-displaying-imported-items-block-gutenberg" target="_blank">block</a>. ' +
        '<br/><br/>' +
        'More template types and options will be made available soon. Have you got a template idea in mind? <a href="https://www.wprssaggregator.com/request-a-template/" target="_blank">Share it with us.</a>'}
        learnMore={'https://kb.wprssaggregator.com/article/457-templates'}
      />

    if (this.router.params.id) {
      minorActions = <div id="" style={{padding: '6px 0'}}>
        <div class="misc-pub-section misc-pub-visibility">
          <a href={this.previewUrl}
             class="wpra-preview-link"
             role="button"
             target="wpra-preview-template"
             style={{marginLeft: '4px', textDecoration: 'none'}}
          >
            Open preview
            <span class="dashicons dashicons-external"/>
          </a>
        </div>
      </div>
    }

    if (this.model.id) {
      shortcode = <div class="wpra-shortcode-copy" title={'Copy chortcode'}>
        <div class="wpra-shortcode-copy__content">
          <strong>Shortcode: </strong>
          <code>{ this.getShortcode() }</code>
        </div>
        <div class="wpra-shortcode-copy__icon">
          <button class="button" onClick={this.copyShortcode}>Copy Shortcode</button>
        </div>
      </div>
    }

    let content = <div>
      <div class="page-title">
        <RouteLink class="back-button" path={back} gate={this.preventLoosingNotSavedData}>
          <span class="dashicons dashicons-arrow-left-alt"></span>
          Templates
        </RouteLink>
        <h1 class="wp-heading-inline">
          {this.router.params.id ? (this.changes.model.name || this.changes.model.slug) : 'Create a New Template'}
        </h1>
        {shortcode}
      </div>
      <div id="poststuff">
        {
          this.isLoading ? <div class="loading-container"/> : <Layout class="metabox-holder columns-2">
            <Main>
              {noticeBlock}
              <Postbox id="template-details" title="Template Details" context={this}>
                <Input type="text"
                       label={'Template name'}
                       value={this.model.name}
                       onInput={(e) => this.model.name = e}
                       disabled={this.model.type === '__built_in'}
                />
                <Input type="select"
                       label={'Template type'}
                       value={this.model.type}
                       options={this.typeOptions}
                       onInput={(e) => this.model.type = e}
                       disabled={this.model.type === '__built_in'}
                       inputDisabled={!WpraTemplates.options.is_type_enabled}
                       description={
                         WpraTemplates.options.is_type_enabled ? null  : '<div class="disable-ignored"><strong class="disable-ignored">🎉 More template types coming soon!</strong>  Have you got a template idea in mind? <a target="_blank" href="https://www.wprssaggregator.com/request-a-template/" class="disable-ignored">Share it with us.</a></div>'
                       }
                />
                {
                  (this.model.type === '__built_in') ?
                    <div class="wpra-info-box">
                      <div class="wpra-info-box__icon">
                        <span class="dashicons dashicons-info"/>
                      </div>
                      <div class="wpra-info-box__text">
                        This is the default template for WP RSS Aggregator. It is used as the fallback template when one is not selected via the shortcode or block. To create a new one, please go back to the Templates List.
                      </div>
                    </div>
                    :
                    null
                }
              </Postbox>
              <Postbox id="template-options" title="Template Options" context={this}>
                <Input type="checkbox"
                       label={'Link title to original article'}
                       value={this.model.options.title_is_link}
                       onInput={(e) => this.model.options.title_is_link = e}
                       title={this.tooltips.options.title_is_link}
                />
                <Input type="number"
                       label={'Title maximum length'}
                       value={this.model.options.title_max_length || ''}
                       placeholder={'No limit'}
                       onInput={(e) => this.model.options.title_max_length = e}
                       title={this.tooltips.options.title_max_length}
                />
                <Input type="number"
                       label={'Number of items to show'}
                       value={this.model.options.limit || ''}
                       onInput={(e) => this.model.options.limit = e}
                       title={this.tooltips.options.limit}
                />

                <Input type="checkbox"
                       label={'Show publish date'}
                       value={this.model.options.date_enabled}
                       onInput={(e) => this.model.options.date_enabled = e}
                       style={{paddingTop: '20px', fontWeight: 'bold'}}
                       title={this.tooltips.options.date_enabled}
                />
                <Input type="text"
                       label={'Date prefix'}
                       value={this.model.options.date_prefix}
                       onInput={(e) => this.model.options.date_prefix = e}
                       disabled={!this.model.options.date_enabled}
                       title={this.tooltips.options.date_prefix}
                />
                <Input type="text"
                       label={'Date format'}
                       value={this.model.options.date_format}
                       onInput={(e) => this.model.options.date_format = e}
                       disabled={this.model.options.date_use_time_ago || !this.model.options.date_enabled}
                       title={this.tooltips.options.date_format}
                />
                <Input type="checkbox"
                       label={'Use "time ago" format'}
                       description={'Example: 20 minutes ago'}
                       value={this.model.options.date_use_time_ago}
                       onInput={(e) => this.model.options.date_use_time_ago = e}
                       disabled={!this.model.options.date_enabled}
                       title={this.tooltips.options.date_use_time_ago}
                />

                <Input type="checkbox"
                       label={'Show source name'}
                       value={this.model.options.source_enabled}
                       onInput={(e) => this.model.options.source_enabled = e}
                       style={{paddingTop: '20px', fontWeight: 'bold'}}
                       title={this.tooltips.options.source_enabled}
                />
                <Input type="text"
                       label={'Source prefix'}
                       value={this.model.options.source_prefix}
                       onInput={(e) => this.model.options.source_prefix = e}
                       disabled={!this.model.options.source_enabled}
                       title={this.tooltips.options.source_prefix}
                />
                <Input type="checkbox"
                       label={'Link source name'}
                       value={this.model.options.source_is_link}
                       onInput={(e) => this.model.options.source_is_link = e}
                       disabled={!this.model.options.source_enabled}
                       title={this.tooltips.options.source_is_link}
                />

                <Input type="checkbox"
                       label={'Show author name'}
                       value={this.model.options.author_enabled}
                       onInput={(e) => this.model.options.author_enabled = e}
                       style={{paddingTop: '20px', fontWeight: 'bold'}}
                       title={this.tooltips.options.author_enabled}
                />
                <Input type="text"
                       label={'Author prefix'}
                       value={this.model.options.author_prefix}
                       onInput={(e) => this.model.options.author_prefix = e}
                       disabled={!this.model.options.author_enabled}
                       title={this.tooltips.options.author_prefix}
                />

                <Input type="checkbox"
                       label={'Pagination'}
                       value={this.model.options.pagination}
                       onInput={(e) => this.model.options.pagination = e}
                       style={{paddingTop: '20px', fontWeight: 'bold'}}
                       title={this.tooltips.options.pagination}
                />
                <Input type="select"
                       label={'Pagination style'}
                       options={WpraTemplates.options.pagination_type}
                       value={this.model.options.pagination_type}
                       onInput={(e) => this.model.options.pagination_type = e}
                       disabled={!this.model.options.pagination}
                       title={this.tooltips.options.pagination_type}
                />

                <Input type="checkbox"
                       label={'Show bullets'}
                       value={this.model.options.bullets_enabled}
                       onInput={(e) => this.model.options.bullets_enabled = e}
                       style={{paddingTop: '20px', fontWeight: 'bold'}}
                       title={this.tooltips.options.bullets_enabled}
                />
                <Input type="select"
                       label={'Bullet style'}
                       options={WpraTemplates.options.bullet_type}
                       value={this.model.options.bullet_type}
                       onInput={(e) => this.model.options.bullet_type = e}
                       disabled={!this.model.options.bullets_enabled}
                       title={this.tooltips.options.bullet_type}
                />
              </Postbox>
            </Main>
            <Sidebar>
              <Postbox id="template-create"
                       title={this.model.id ? 'Update Template' : 'Create Template'}
                       submit={true}
                       class={'wpra-postbox-last'}
                       context={this}
              >
                <div class="submitbox" id="submitpost">
                  {minorActions}

                  <div id="major-publishing-actions">
                    <div id="delete-action">
                      {
                        this.isChanged() ? <a href="#" class="submitdelete" onClick={(e) => {
                          e.preventDefault()
                          this.cancelChanges()
                        }}>
                          Cancel Changes
                        </a> : null
                      }
                    </div>

                    <div id="publishing-action">
                      <Button class="button-primary button-large"
                              loading={this.isSaving}
                              nativeOnClick={this.save}
                      >
                        {this.model.id ? 'Save' : 'Publish'}
                      </Button>
                    </div>
                    <div class="clear"></div>
                  </div>
                </div>
              </Postbox>
              <Postbox id="template-link-preferences" title="Link Preferences" context={this}>
                <p style={{opacity: .65}}>
                  These options apply to all links within this template.
                </p>
                <Input type="checkbox"
                       label={'Set links as nofollow'}
                       value={this.model.options.links_nofollow}
                       onInput={(e) => this.model.options.links_nofollow = e}
                       title={this.tooltips.options.links_nofollow}
                />
                <Input type="select"
                       label={'Open links behaviour'}
                       class="form-input--vertical"
                       value={this.model.options.links_behavior}
                       options={WpraTemplates.options.links_behavior}
                       onInput={(e) => this.model.options.links_behavior = e}
                       title={this.tooltips.options.links_behavior}
                />
                <Input type="select"
                       label={'Video embed link type'}
                       description={'This will not affect already imported feed items.'}
                       class="form-input--vertical"
                       value={this.model.options.links_video_embed_page}
                       options={WpraTemplates.options.links_video_embed_page}
                       onInput={(e) => this.model.options.links_video_embed_page = e}
                       title={this.tooltips.options.links_video_embed_page}
                />
              </Postbox>
              <Postbox id="template-custom-css" title="Custom Style" context={this}>
                <Input type="text"
                       class="form-input--vertical"
                       label={'Custom CSS class name'}
                       value={this.model.options.custom_css_classname}
                       onInput={(e) => this.model.options.custom_css_classname = e}
                       title={this.tooltips.options.custom_css_classname}
                />
              </Postbox>
            </Sidebar>
          </Layout>
        }
      </div>
    </div>
    return this.hooks.apply('wpra-templates-form', this, content)
  }
}
