<script>
  import Postbox from './Postbox'
  import Main from './Main'
  import Sidebar from './Sidebar'
  import Layout from './Layout'
  import RouteLink from './RouteLink'
  import Input from './Input'
  import Button from './Button'
  import deepmerge from './deepmerge'
  import DataChangesAware from './DataChangesAware'
  import jsonClone from './jsonClone'

  export default {
    mixins: [ DataChangesAware ],
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
        baseUrl: WpraTemplates.base_url,
        isSaving: false,
        isLoading: false,
      }
    },
    inject: [
      'hooks',
      'http',
      'router'
    ],
    mounted () {
      this.resolveEditingItem()
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
        return Object.keys(this.model)
          .filter(key => key !== 'id')
          .reduce((acc, key) => {
            acc[key] = this.model[key]
            return acc
          }, {})
      },
    },
    render () {
      let back = {
        name: 'templates'
      }
      let content = <div>
          <div class="page-title">
            <RouteLink class="back-button" path={back}>
              <span class="dashicons dashicons-arrow-left-alt"></span>
            </RouteLink>
            <h1 class="wp-heading-inline">
              {this.router.params.id ? 'Edit Template' : 'New Template'}
              {
                this.router.params.id ? <span style={{opacity: '.5', marginLeft: '12px', display: 'inline-block'}}>
                    #{ this.router.params.id }
                  </span> : null
              }
            </h1>
          </div>
        <div id="poststuff">
          {
            this.isLoading ? <div class="loading-container"></div> : <Layout class="metabox-holder columns-2">
              <Sidebar>
                <Postbox id="template-create"
                         title={this.model.id ? 'Update Template' : 'Create Template'}
                         submit={true}
                >
                  <div class="submitbox" id="submitpost">
                    <div id="minor-publishing"></div>
                    <div id="major-publishing-actions">
                      <div id="delete-action">
                        {
                          this.isChanged() ? <a href="#" onClick={(e) => {e.preventDefault(); this.cancelChanges()}}>
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
                <Postbox id="template-link-preferences" title="Link Preferences">
                  <Input type="checkbox"
                         label={'Set links as nofollow'}
                         value={this.model.options.links_nofollow}
                         onInput={(e) => this.model.options.links_nofollow = e}
                  />
                  <Input type="select"
                         label={'Open links behaviour'}
                         class="form-input--vertical"
                         value={this.model.options.links_behavior}
                         options={WpraTemplates.options.links_behavior}
                         onInput={(e) => this.model.options.links_behavior = e}
                  />
                </Postbox>
                <Postbox id="template-custom-css" title="Custom CSS class">
                  <Input type="text"
                         placeholder={'Enter custom CSS class name'}
                         value={this.model.options.custom_css_classname}
                         onInput={(e) => this.model.options.custom_css_classname = e}
                  />
                </Postbox>
              </Sidebar>
              <Main>
                <Postbox id="template-details" title="Template Details">
                  <Input type="text"
                         label={'Template Name'}
                         value={this.model.name}
                         onInput={(e) => this.model.name = e}
                         disabled={this.model.type === '__built_in'}
                  />
                  <Input type="select"
                         label={'Template Type'}
                         value={this.model.type}
                         options={this.typeOptions}
                         onInput={(e) => this.model.type = e}
                         disabled={this.model.type === '__built_in'}
                  />
                </Postbox>
                <Postbox id="template-options" title="Template Options">
                  <Input type="checkbox"
                         label={'Link title to original article'}
                         description="Whether we should use a title as a link to the original article."
                         value={this.model.options.title_is_link}
                         onInput={(e) => this.model.options.title_is_link = e}
                  />
                  <Input type="number"
                         label={'Title maximum length'}
                         value={this.model.options.title_max_length}
                         onInput={(e) => this.model.options.title_max_length = e}
                  />

                  <Input type="checkbox"
                         label={'Show publish date'}
                         value={this.model.options.date_enabled}
                         onInput={(e) => this.model.options.date_enabled = e}
                  />
                  <Input type="text"
                         label={'Text preceeding date'}
                         value={this.model.options.date_prefix}
                         onInput={(e) => this.model.options.date_prefix = e}
                  />
                  <Input type="text"
                         label={'Date format'}
                         value={this.model.options.date_format}
                         onInput={(e) => this.model.options.date_format = e}
                  />
                  <Input type="checkbox"
                         label={'Use "time ago" format'}
                         value={this.model.options.date_use_time_ago}
                         onInput={(e) => this.model.options.date_use_time_ago = e}
                  />

                  <Input type="checkbox"
                         label={'Show source name'}
                         value={this.model.options.source_enabled}
                         onInput={(e) => this.model.options.source_enabled = e}
                  />
                  <Input type="text"
                         label={'Source prefix'}
                         value={this.model.options.source_prefix}
                         onInput={(e) => this.model.options.source_prefix = e}
                  />
                  <Input type="checkbox"
                         label={'Link source name'}
                         value={this.model.options.source_is_link}
                         onInput={(e) => this.model.options.source_is_link = e}
                  />

                  <Input type="checkbox"
                         label={'Show author name'}
                         value={this.model.options.author_enabled}
                         onInput={(e) => this.model.options.author_enabled = e}
                  />
                  <Input type="text"
                         label={'Text preceeding author name'}
                         value={this.model.options.author_prefix}
                         onInput={(e) => this.model.options.author_prefix = e}
                  />

                  <Input type="checkbox"
                         label={'Pagination'}
                         value={this.model.options.pagination}
                         onInput={(e) => this.model.options.pagination = e}
                  />
                  <Input type="select"
                         label={'Pagination style'}
                         options={WpraTemplates.options.pagination_type}
                         value={this.model.options.pagination_type}
                         onInput={(e) => this.model.options.pagination_type = e}
                  />

                  <Input type="checkbox"
                         label={'Show bullets'}
                         value={this.model.options.bullets_enabled}
                         onInput={(e) => this.model.options.bullets_enabled = e}
                  />
                  <Input type="select"
                         label={'Bullet style'}
                         options={WpraTemplates.options.bullet_type}
                         value={this.model.options.bullet_type}
                         onInput={(e) => this.model.options.bullet_type = e}
                  />
                </Postbox>
              </Main>
            </Layout>
          }
        </div>
      </div>
      return this.hooks.apply('wpra-templates-form', this, content)
    }
  }
</script>
