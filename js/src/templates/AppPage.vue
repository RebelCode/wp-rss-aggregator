<script>
  import Postbox from './Postbox'
  import Main from './Main'
  import Sidebar from './Sidebar'
  import Layout from './Layout'
  export default {
    data () {
      return {
      }
    },
    inject: [
      'hooks'
    ],
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
                Template Details
              </Postbox>
              <Postbox id="template-options" title="Template Options">
                Template Options
              </Postbox>
            </Main>
          </Layout>
      </div>
      return this.hooks.apply('wpra-templates-form', this, content)
    }
  }
</script>
