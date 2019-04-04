import VueTable from 'vue-wp-list-table/vue-wp-list-table.common'
import RouteLink from 'app/components/RouteLink'
import Input from 'app/components/Input'
import BottomPanel from 'app/components/BottomPanel'
import jsonClone from 'app/utils/jsonClone'

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

      filters: WpraTemplates.options.type,

      checked: [],

      filter: {
        paged: parseInt(this.router.params.paged || 1),
        type: this.router.params.type || '',
        s: this.router.params.s || '',
      },

      baseUrl: WpraTemplates.base_url,

      total: 0,
    }
  },
  inject: [
    'hooks',
    'http',
    'router',
  ],
  computed: {
    totalPages () {
      return Math.ceil(this.total / 20)
    },
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
    navigated () {
      Object.keys(this.filter).forEach(key => {
        this.filter[key] = this.router.params[key] || ''
      })
      this.filter.paged = parseInt(this.filter.paged || 1)
      this.fetchList()
    },

    fetchList () {
      this.loading = true

      let params = this.getParams()

      let paged = parseInt(params.paged)
      delete params.paged
      if (!!paged && paged !== 1) {
        params['page'] = paged
      }

      return this.http.get(this.baseUrl, {
        params
      }).then((response) => {
        this.list = response.data.items
        this.total = response.data.count
      }).finally(() => {
        this.loading = false
      })
    },

    deleteTemplate (id) {
      if (!confirm('Are you sure you want to delete this template?')) {
        return
      }
      this.loading = true
      return this.http.delete(`${this.baseUrl}/${id}`).then(() => {
        return this.fetchList()
      }).then(() => {
        this.loading = false
      })
    },

    bulkDelete () {
      if (!confirm('Are you sure you want to delete selected templates?')) {
        return
      }
      this.loading = true
      return this.http.delete(this.baseUrl, {
        params: {
          ids: this.checked
        }
      }).then(() => {
        this.checked = []
        this.$refs.table.checkedItems = []
        return this.fetchList()
      }).then(() => {
        this.loading = false
      })
    },

    duplicateTemplate (row) {
      let template = jsonClone(row)

      delete template.id

      if (template.type === '__built_in') {
        delete template.type
      }

      this.$store.commit('templates/updatePreset', template)
      this.router.navigate({
        name: 'templates',
        params: {
          action: 'new',
        }
      })
    },

    getPreviewLink (row) {
      return `${WpraGlobal.admin_base_url}?wpra_preview_template=${row.id}`
    },

    createTemplate () {
      this.$store.commit('templates/updatePreset', {})
      this.router.navigate({
        name: 'templates',
        params: {
          action: 'new',
        }
      })
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
      this.router.mergeParams(this.getParams())
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
      name: ({row}) => {
        return [
          <div><strong>{row.name}</strong><small style={{paddingLeft: '4px'}}>ID: {row.id}</small></div>,
          <div class="row-actions">
              <span className="edit">
                <RouteLink path={editPath(row.id)}>Edit</RouteLink> |
              </span>
            <span class="inline" style={{paddingLeft: '4px'}}>
                <a href="#"
                   onClick={(e) => {
                     e.preventDefault()
                     this.duplicateTemplate(row)
                   }}
                >Duplicate</a> {row.type !== '__built_in' ? '|' : ''}
              </span>
            {
              (row.type !== '__built_in')
                ?
                <span class="trash" style={{paddingLeft: '4px'}} onClick={(e) => {
                  e.preventDefault()
                  this.deleteTemplate(row.id)
                }}>
                      <a href="#" class="submitdelete" aria-label="Delete Item">Delete</a>
                    </span>
                :
                null
            }
          </div>
        ]
      },
      style: ({row}) => {
        return [
          <div>{this.filters[row.type]}</div>
        ]
      },
      previewTemplate: ({row}) => {
        return [
          <div>
            <a href={this.getPreviewLink(row)} target="_blank">
              <span class="dashicons dashicons-desktop"></span>
            </a>
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
                 onInput={(value) => {
                   this.filter.type = value
                   this.submitFilter()
                 }}
          />
        ]
      }
    })

    let content = <div>
      <h1 class="wp-heading-inline">Templates</h1>
      <a class="page-title-action"
         href="#"
         onClick={e => {
           e.preventDefault()
           this.createTemplate()
         }}
      >Add New</a>

      <p class="search-box" style={{padding: '10px'}}>
        <label class="screen-reader-text" for="post-search-input">Search Templates:</label>
        <input type="search"
               id="post-search-input"
               name="s"
               value={this.filter.s}
               onInput={e => this.filter.s = e.target.value}
               onKeyup:enter={this.submitFilter}
        />
        <input type="submit" id="search-submit" class="button" value="Search Templates"
               onClick={this.submitFilter}
        />
      </p>

      <hr class="wp-header-end"/>

      <VueTable
        onChecked={this.setChecked}
        onPagination={page => {
          this.filter.paged = page
          this.submitFilter()
        }}
        columns={this.columns}
        rows={this.list}
        loading={this.loading}
        totalItems={this.total}
        perPage={20}
        totalPages={this.totalPages}
        currentPage={this.filter.paged}
        ref="table"
        notFound="No templates found."
        scopedSlots={
          cells
        }
      />

      {
        this.checked.length ? <BottomPanel>
          <div class="flex-row">
            <div class="flex-col">
              <div class="wpra-bottom-panel__title">Bulk Actions</div>
              <a href="#" onClick={(e) => {
                e.preventDefault()
                this.bulkDelete()
              }}>Trash</a>
            </div>
          </div>
        </BottomPanel> : null
      }
    </div>
    return this.hooks.apply('wpra-templates-list', this, content)
  }
}
