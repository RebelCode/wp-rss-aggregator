<script>
  import Postbox from './Postbox'
  import Main from './Main'
  import Sidebar from './Sidebar'
  import Layout from './Layout'
  import Input from './Input'

  export default {
    data () {
      return {
        typeOptions: Object.keys(WpraTemplates.options.type)
          .filter(key => key[0] !== '_')
          .reduce((acc, key) => {
            acc[key] = WpraTemplates.options.type[key]
            return acc
          }, {}),
        model: WpraTemplates.model_schema,
        validation: WpraTemplates.model_schema,
        baseUrl: WpraTemplates.base_url,
      }
    },
    inject: [
      'hooks',
      'http',
    ],
    methods: {
      save () {
        this.http.post(this.baseUrl, this.prepareModel()).then(response => {
          console.info('yeah!', response)
        })
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
      let content = <div id="poststuff">
          <Layout class="metabox-holder columns-2">
            <Sidebar>
              <Postbox id="template-create" title="Create" submit={true}>
                <div class="submitbox" id="submitpost">
                  <div id="minor-publishing"></div>
                  <div id="major-publishing-actions">
                    <div id="delete-action">
                      <a class="submitdelete deletion"
                         href="http://scotchbox.local/wp/wp-admin/post.php?post=1593&amp;action=trash&amp;_wpnonce=fceeffe9f0">Move
                        to Trash</a>
                    </div>

                    <div id="publishing-action">
                      <span class="spinner"></span>
                      <input name="original_publish" type="hidden" id="original_publish" value="Publish"/>
                      <input type="submit" name="publish" id="publish" class="button button-primary button-large"
                             onClick={this.save}
                             value="Publish"/>
                    </div>
                    <div class="clear"></div>
                  </div>
                </div>
              </Postbox>
              <Postbox id="template-link-preferences" title="Link Preferences">
                <Input type="checkbox"
                       label={'Set links as nofollow'}
                       value={this.model.options.links_nofollow}
                       onInput={ (e) => this.model.options.links_nofollow = e }
                />
                <Input type="select"
                       label={'Open links behaviour'}
                       value={this.model.options.links_behavior}
                       options={WpraTemplates.options.links_behavior}
                       onInput={ (e) => this.model.options.links_behavior = e }
                />
              </Postbox>
              <Postbox id="template-custom-css" title="Custom CSS class">
                <Input type="text"
                       placeholder={'Enter custom CSS class name'}
                       value={this.model.options.custom_css_classname}
                       onInput={ (e) => this.model.options.custom_css_classname = e }
                />
              </Postbox>
            </Sidebar>
            <Main>
              <Postbox id="template-details" title="Template Details">
                <Input type="text"
                       label={'Template Name'}
                       value={this.model.name}
                       onInput={ (e) => this.model.name = e }
                />
                <Input type="select"
                       label={'Template Type'}
                       value={this.model.type}
                       options={this.typeOptions}
                       onInput={ (e) => this.model.type = e }
                />
              </Postbox>
              <Postbox id="template-options" title="Template Options">
                <Input type="checkbox"
                       label={'Link title to original article'}
                       description="Whether we should use a title as a link to the original article."
                       value={this.model.options.title_is_link}
                       onInput={ (e) => this.model.options.title_is_link = e }
                />
                <Input type="number"
                       label={'Title maximum length'}
                       value={this.model.options.title_max_length}
                       onInput={ (e) => this.model.options.title_max_length = e }
                />

                <Input type="checkbox"
                       label={'Show publish date'}
                       value={this.model.options.date_enabled}
                       onInput={ (e) => this.model.options.date_enabled = e }
                />
                <Input type="text"
                       label={'Text preceeding date'}
                       value={this.model.options.date_prefix}
                       onInput={ (e) => this.model.options.date_prefix = e }
                />
                <Input type="text"
                       label={'Date format'}
                       value={this.model.options.date_format}
                       onInput={ (e) => this.model.options.date_format = e }
                />
                <Input type="checkbox"
                       label={'Use "time ago" format'}
                       value={this.model.options.date_use_time_ago}
                       onInput={ (e) => this.model.options.date_use_time_ago = e }
                />

                <Input type="checkbox"
                       label={'Show source name'}
                       value={this.model.options.source_enabled}
                       onInput={ (e) => this.model.options.source_enabled = e }
                />
                <Input type="text"
                       label={'Source prefix'}
                       value={this.model.options.source_prefix}
                       onInput={ (e) => this.model.options.source_prefix = e }
                />
                <Input type="checkbox"
                       label={'Link source name'}
                       value={this.model.options.source_is_link}
                       onInput={ (e) => this.model.options.source_is_link = e }
                />

                <Input type="checkbox"
                       label={'Show author name'}
                       value={this.model.options.author_enabled}
                       onInput={ (e) => this.model.options.author_enabled = e }
                />
                <Input type="text"
                       label={'Text preceeding author name'}
                       value={this.model.options.author_prefix}
                       onInput={ (e) => this.model.options.author_prefix = e }
                />

                <Input type="checkbox"
                       label={'Pagination'}
                       value={this.model.options.pagination}
                       onInput={ (e) => this.model.options.pagination = e }
                />
                <Input type="select"
                       label={'Pagination style'}
                       options={WpraTemplates.options.pagination_type}
                       value={this.model.options.pagination_type}
                       onInput={ (e) => this.model.options.pagination_type = e }
                />

                <Input type="checkbox"
                       label={'Show bullets'}
                       value={this.model.options.bullets_enabled}
                       onInput={ (e) => this.model.options.bullets_enabled = e }
                />
                <Input type="select"
                       label={'Bullet style'}
                       options={WpraTemplates.options.bullet_type}
                       value={this.model.options.bullet_type}
                       onInput={ (e) => this.model.options.bullet_type = e }
                />
              </Postbox>
            </Main>
          </Layout>
      </div>
      return this.hooks.apply('wpra-templates-form', this, content)
    }
  }
</script>
