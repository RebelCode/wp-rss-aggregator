<script>
  import VueTable from 'vue-wp-list-table/dist/vue-wp-list-table.common'
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

        list: [],

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
    methods: {
      fetchList () {
        this.loading = true
        return this.http.get(this.baseUrl).then((response) => {
          this.list = response.data
          console.info(response)
        }).finally(() => {
          this.loading = false
        })
      }
    },
    render () {
      let cells = this.hooks.apply('wpra-templates-list-cells', this, {
        name: function ({ row }) {
          return [
            <div><strong>{ row.title }</strong> <small>ID: { row.id }</small></div>,
            <div class="row-actions">
              <span class="edit">
                <a href="http://scotchbox.local/wp/wp-admin/post.php?post=1556&amp;action=edit" aria-label="Edit “asdasdasd”">Edit</a>
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

      let content = <div>
        <h1 class="wp-heading-inline">Templates</h1>
        <a href="#" onClick={(e) => {e.preventDefault(); this.router.navigate({ name: 'edit' })}} class="page-title-action">Add New</a>
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
