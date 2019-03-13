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
              <Postbox id="template-create" title="Create">
                Template Create
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
