<script>
  import VueTable from 'vue-wp-list-table/dist/vue-wp-list-table.common'
  export default {
    data () {
      return {
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

        list: [{
          id: 1,
          title: 'Foo',
          template_type: 'list',
        }, {
          id: 2,
          title: 'Bar',
          template_type: '__built_in',
        }]
      }
    },
    inject: [
      'hooks'
    ],
    mounted () {
      console.info('Welcome, Neo', this, this.hooks)
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
            <div>{ row.template_type }</div>
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
        <VueTable
          columns={this.columns}
          rows={this.list}
          scopedSlots={
            cells
          }
        >
        </VueTable>
      </div>
      return this.hooks.apply('wpra-templates-list', this, content)
    }
  }
</script>
