import VueTable from '@rebelcode/vue-wp-list-table/dist/vue-wp-list-table.common'
import RouteLink from 'app/components/RouteLink'
import Input from 'app/components/Input'
import BottomPanel from 'app/components/BottomPanel'
import jsonClone from 'app/utils/jsonClone'
import NoticeBlock from 'app/components/NoticeBlock'
import collect from 'app/utils/Collection'

export default {
  data () {
    return {
      loading: false,

      columns: {
        name: {
          label: ('Template Name'),
          class: 'column-primary'
        },
        style: {
          label: ('Template Type'),
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
      if (!confirm('Are you sure you want to delete this template? If this template is being used in a shortcode or Gutenberg block anywhere on your site, the default template will be used instead.')) {
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
      if (!confirm('Are you sure you want to delete these templates? If a template is being used in a shortcode or Gutenberg block anywhere on your site, the default template will be used instead.')) {
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

      // add copy to the title so it is mor obvious for the user that this is duplicate.
      template.name = `${template.name} (Copy)`

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
      this.checked = values.filter(id => {
        return collect(this.list).find(id, {}).type !== '__built_in'
      })
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
    },

    getRowClass (row) {
      return row.type === '__built_in' ? 'built-in' : ''
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
          <div>
            <strong><RouteLink path={editPath(row.id)}>{row.name}</RouteLink></strong>
            <small style={{paddingLeft: '4px', opacity: '0.6'}}>{row.slug}</small>
            {
              (row.type === '__built_in') ?
                <span style={{opacity: '0.6', display: 'block'}}>
                  This is the default feed template. To create your own, either duplicate it or click "Add New" above.
                </span>
                :
                null
            }
          </div>,
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
            <a href={this.getPreviewLink(row)}
               target="wpra-preview-template"
               class="wpra-preview-link"
            >
              Open preview <span class="dashicons dashicons-external"></span>
            </a>
          </div>
        ]
      },
      filters: () => {
        let templateTypes = Object.keys(WpraTemplates.options.type)
          .filter(key => key[0] !== '_')
          .reduce((carry, key) => {
            carry[key] = WpraTemplates.options.type[key]
            return carry
          }, {
            'all': 'Select Template Type',
          })
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

      <p class="search-box" style={{padding: '10px 0'}}>
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

      <NoticeBlock
        id={'templates-introduction'}
        title={'🎉 Welcome to Templates for WP RSS Aggregator!'}
        body={'As of version 4.13, we have introduced the concept of templates to replace the display settings that were ' +
        'previously available in the WP RSS Aggregator settings. These templates provide you with much more ' +
        'flexibility and new designs. They also come with a revamped <a target="_blank" href="https://kb.wprssaggregator.com/article/54-displaying-imported-items-shortcode">TinyMCE shortcode button</a> for the Classic Editor and ' +
        'a <em><a href="https://kb.wprssaggregator.com/article/454-displaying-imported-items-block-gutenberg" target="_blank">brand new block</a></em> for those using WP 5.0+ with the Gutenberg block editor!<br/><br/>There are new template types coming ' +
        'your way in the coming weeks, but for now, the <em>list template type</em> replicates the previous options. ' +
        'Please note that the <em>Default</em> template below is set up using your pre-existing display options, nothing is lost or changed.'}
        learnMore={'https://www.wprssaggregator.com/core-version-4-13-celebrating-one-million-downloads/'}
        visible={!!WpraGlobal.is_existing_user}
      />

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
        rowClass={this.getRowClass}
        class={{
          'wpra-no-cb': this.list.length === 0 || (this.list.length === 1 && this.list[0].type === '__built_in')
        }}
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
              }}>Delete</a>
            </div>
          </div>
        </BottomPanel> : null
      }
    </div>
    return this.hooks.apply('wpra-templates-list', this, content)
  }
}
