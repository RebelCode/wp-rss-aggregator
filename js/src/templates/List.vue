<script>
  import VueTable from 'vue-wp-list-table/dist/vue-wp-list-table.common'
  import RouteLink from './RouteLink'
  import Input from './Input'
  import BottomPanel from './BottomPanel'

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

        filters: WpraTemplates.options.filter_types,

        checked: [],

        filter: {
          type: this.router.params.type ? this.router.params.type : '',
          s: this.router.params.s ? this.router.params.s : ''
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

      this.router.onRouteNavigate(({ params }) => {
        Object.keys(this.filter).forEach(key => {
          this.filter[key] = params[key] || ''
        })
        this.fetchList()
      })
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
        const params = this.getParams()
        return this.http.get(this.baseUrl, {
          params
        }).then((response) => {
          this.list = response.data.items
        }).finally(() => {
          this.loading = false
        })
      },

      deleteTemplate (id) {
        this.loading = true
        return this.http.delete(`${this.baseUrl}/${id}`).then(() => {
          return this.fetchList()
        }).then(() => {
          this.loading = false
        })
      },

      duplicateTemplate (row) {

      },

      setChecked (values) {
        this.checked = values
      },

      getParams () {
        return Object.keys(this.filter).filter(key => {
          return !!this.filter[key] && this.filter[key] !== 'all'
        }).reduce((acc, key) => {
          acc[key] = this.filter[key]
          return acc
        }, {})
      },

      setFilter (name, value) {
        this.filter[name] = value
      },

      submitFilter () {
        console.warn('submitting params', {
          params: this.getParams()
        })
        this.router.mergeParams(this.getParams())
        this.fetchList()
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
        name: ({ row }) => {
          return [
            <div><strong>{ row.name }</strong> <small>ID: { row.id }</small></div>,
            <div class="row-actions">{
                (row.type !== '__built_in')
                  ?
                  <span class="edit">
                    <RouteLink path={editPath(row.id)}>Edit</RouteLink> |
                  </span>
                  :
                  null
              }
              <span class="inline" style={{paddingLeft: row.type !== '__built_in' ? '4px' : 0}}
                onClick={this.duplicateTemplate(row)}
              >
                <a href="#">Duplicate</a> {row.type !== '__built_in' ? '|' : ''}
              </span>
              {
                (row.type !== '__built_in')
                  ?
                    <span class="trash" style={{paddingLeft: '4px'}} onClick={this.deleteTemplate(row.id)}>
                      <a href="#" class="submitdelete" aria-label="Delete Item">Delete</a>
                    </span>
                  :
                  null
              }
            </div>
          ]
        },
        style: ({ row }) => {
          return [
            <div>{ this.filters[row.type] }</div>
          ]
        },
        previewTemplate: function ({ row }) {
          return [
            <div>
              <span class="dashicons dashicons-desktop"></span>
            </div>
          ]
        },
        filters: () => {
          const templateTypes = {
            'all': 'Select Template Style',
            'list': 'List',
            'grid': 'Grid',
          }
          return [
            <Input type="select"
                   style={{margin: 0}}
                   options={templateTypes}
                   value={this.filter.type}
                   onInput={ (value) => { this.filter.type = value; this.submitFilter() } }
            />
          ]
        }
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

        <p class="search-box" style={{padding: '10px'}}>
          <label class="screen-reader-text" for="post-search-input">Search Templates:</label>
          <input type="search"
                 id="post-search-input"
                 name="s"
                 value={this.filter.s}
                 onInput={ e => this.filter.s = e.target.value }
                 onKeyup:enter={this.submitFilter}
          />
          <input type="submit" id="search-submit" class="button" value="Search Templates"
            onClick={this.submitFilter}
          />
        </p>

        <hr class="wp-header-end"/>

        <VueTable
          onChecked={this.setChecked}
          columns={this.columns}
          rows={this.list}
          loading={this.loading}
          scopedSlots={
            cells
          }
        />

        {
          this.checked.length ? <BottomPanel>
              <div class="flex-row">
                <div class="flex-col">
                  <div class="wpra-bottom-panel__title">Bulk Actions</div>
                  <a href="#">Trash</a>
                </div>
              </div>
            </BottomPanel> : null
        }
      </div>
      return this.hooks.apply('wpra-templates-list', this, content)
    }
  }
</script>
