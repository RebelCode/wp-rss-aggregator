<script>
  import VueTable from 'vue-wp-list-table/dist/vue-wp-list-table.common'
  import RouteLink from './RouteLink'

  export default {
    data () {
      return {
        loading: false,

        columns: {
          name: {
            label: ('Template Name'),
          },
          style: {
            label: ('Template Style'),
          },
          previewTemplate: {
            label: ('Preview')
          }
        },

        baseUrl: WpraTemplates.base_url,
      }
    },
    inject: [
      'hooks',
      'http',
      'router',
    ],
    mounted () {
      this.fetchList()
    },
    computed: {
      list: {
        get () {
          return this.$store.state.templates.items
        },
        set (value) {
          this.$store.commit('templates/set', value)
        },
      }
    },
    methods: {
      fetchList () {
        this.loading = true
        return this.http.get(this.baseUrl).then((response) => {
          this.list = response.data
        }).finally(() => {
          this.loading = false
        })
      }
    },
    render () {
      const editPath = (id) => {
        return {
          name: 'templates',
          params: {
            action: 'edit',
            id,
          }
        }
      }

      let cells = this.hooks.apply('wpra-templates-list-cells', this, {
        name: function ({ row }) {
          return [
            <div><strong>{ row.name }</strong> <small>ID: { row.id }</small></div>,
            <div class="row-actions">
              <span class="edit">
                <RouteLink path={editPath(row.id)}>Edit</RouteLink>
              </span>
            </div>
          ]
        },
        style: function ({ row }) {
          return [
            <div>{ row.type }</div>
          ]
        },
        previewTemplate: function ({ row }) {
          return [
            <div>
              <span class="dashicons dashicons-desktop"></span>
            </div>
          ]
        },
      })

      let pathNew = {
        name: 'templates',
        params: {
          action: 'new',
        }
      }

      let content = <div>
        <h1 class="wp-heading-inline">Templates</h1>
        <RouteLink path={pathNew} class="page-title-action">Add New</RouteLink>
        <hr class="wp-header-end"/>

        <VueTable
          columns={this.columns}
          rows={this.list}
          loading={this.loading}
          scopedSlots={
            cells
          }
        />
      </div>
      return this.hooks.apply('wpra-templates-list', this, content)
    }
  }
</script>
