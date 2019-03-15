<script>
  import Postbox from './Postbox'
  import Main from './Main'
  import Sidebar from './Sidebar'
  import Layout from './Layout'
  import Input from './Input'
  import axios from 'axios'

  export default {
    data () {
      return {
        typeOptions: Object.keys(WpraTemplates.type_options)
          .filter(key => key[0] !== '_')
          .reduce((acc, key) => {
            acc[key] = WpraTemplates.type_options[key]
            return acc
          }, {}),
        model: WpraTemplates.model_schema,
        validation: WpraTemplates.model_schema,
        baseUrl: WpraTemplates.base_url,
      }
    },
    inject: [
      'hooks'
    ],
    methods: {
      save () {
        const client = axios.create({
          headers: {
            'X-WP-Nonce': WpraTemplates.nonce,
          }
        })

        client.post(this.baseUrl, this.prepareModel()).then(response => {
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
                Template Link Preferences
              </Postbox>
              <Postbox id="template-custom-css" title="Custom CSS class">
                Template Custom CSS class
              </Postbox>
            </Sidebar>
            <Main>
              <Postbox id="template-details" title="Template Details">
                <Input type="text"
                       label={'Template Name'}
                       value={this.model.title}
                       onInput={ (e) => this.model.title = e }
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
              </Postbox>
            </Main>
          </Layout>
      </div>
      return this.hooks.apply('wpra-templates-form', this, content)
    }
  }
</script>
