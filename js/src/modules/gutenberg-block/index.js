require('css/src/gutenberg-block/index.scss')

import { __ } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls } from '@wordpress/editor'
import {
  ToggleControl,
  ServerSideRender,
  TextControl,
  TextareaControl,
  BaseControl,
  PanelBody,
  PanelRow,
  Spinner,
  Placeholder,
  FormTokenField,
  SelectControl,
} from '@wordpress/components'
import MultipleSelectControl from './components/MultipleSelectControl'

// Default template is selected by default.
let selectedTemplate = WPRA_BLOCK.templates[0]

// Selected template field getter. Additional function can be passed.
const getTemplateDefault = (field, wrapper = val => val, def = 0) => selectedTemplate[field] ? wrapper(selectedTemplate[field]) : def

// Helps to not override attributes that selected manually by user.
let templateLock = {}

// Whether the block is loaded initial information.
let _isLoaded = false

registerBlockType('wpra-shortcode/wpra-shortcode', {
  title: __('WP RSS Aggregator Feeds'),
  description: __('Display feed items imported using WP RSS Aggregator.'),
  icon: 'rss',
  category: 'widgets',

  // Remove to make block editable in HTML mode.
  supportHTML: false,

  attributes: {
    isAll: {
      type: 'boolean',
      default: true
    },
    template: {
      type: 'string',
      default: 'default'
    },
    pagination: {
      type: 'boolean',
      default: true
    },
    limit: {
      type: 'number',
    },
    page: {
      type: 'number',
    },
    exclude: {
      type: 'string'
    },
    source: {
      type: 'string'
    }
  },

  state: {
    foo: 'bar'
  },

  /**
   * Called when Gutenberg initially loads the block.
   */
  edit: function (props) {
    /*
     * If block is not loaded, check whether we should block auto limit selection.
     * It will be blocked if user selected entered limit value different from template's default.
     */
    if (!_isLoaded && props.attributes.template) {
      selectedTemplate = WPRA_BLOCK.templates.find(item => item.value === (props.attributes.template || 'default'))

      if (parseInt(props.attributes.limit) !== getTemplateDefault('limit', parseInt)) {
        templateLock['limit'] = true
      }

      if (!!props.attributes.pagination !== getTemplateDefault('pagination', v => !!v, false)) {
        templateLock['pagination'] = true
      }

      _isLoaded = true
    }

    const etWarning = WPRA_BLOCK.is_et_active ? <p style={{fontStyle: 'italic'}}>
      Excerpts & Thumbnails is incompatible with the WP RSS Aggregator Feeds block. <a href="https://kb.wprssaggregator.com/article/459-using-excerpts-thumbnails-with-templates" target={'_blank'}>Learn more</a>.
    </p> : null

    return <div>
      <ServerSideRender
        block={'wpra-shortcode/wpra-shortcode'}
        attributes={props.attributes}
        className={'wpra-gutenberg-block'}
      />
      <InspectorControls>
        <PanelBody
          title={__('Feed Sources')}
          initialOpen={true}
        >
          <ToggleControl
            label={__('Show all Feed Sources ')}
            checked={props.attributes.isAll}
            onChange={(value) => {
              props.setAttributes({isAll: value})
              props.setAttributes({exclude: ''})
              props.setAttributes({source: ''})
            }}
          />
          <MultipleSelectControl
            label={props.attributes.isAll ? __('Feed Sources to Exclude') : __('Feed Sources to Show')}
            key={'select'}
            help={__('Start typing to search feed sources by name')}
            value={((props.attributes.isAll ? props.attributes.exclude : props.attributes.source) || '').split(',').map(item => parseInt(item))}
            onChange={(selected) => {
              selected = selected.join(',')
              if (props.attributes.isAll) {
                props.setAttributes({exclude: selected})
                props.setAttributes({source: ''})
                return
              }
              props.setAttributes({exclude: ''})
              props.setAttributes({source: selected})
            }}
          />
        </PanelBody>
        <PanelBody
          title={__('Display Options')}
          initialOpen={false}
        >
          {etWarning}
          <SelectControl
            label={ __( 'Select Template' ) }
            value={ props.attributes.template }
            onChange={(template) => {
              selectedTemplate = WPRA_BLOCK.templates.find(item => item.value === template)
              props.setAttributes({template: template || ''})
              if (!templateLock['limit']) {
                props.setAttributes({limit: getTemplateDefault('limit', parseInt, 15)})
              }
              if (!templateLock['pagination']) {
                props.setAttributes({pagination: getTemplateDefault('pagination', v => !!v, false)})
              }
            }}
            options={WPRA_BLOCK.templates}
          />
          <TextControl
            label={__('Feed Limit')}
            help={__('Number of feed items to display')}
            placeholder={getTemplateDefault('limit', parseInt)}
            type={'number'}
            min={1}
            value={props.attributes.limit || getTemplateDefault('limit', parseInt)}
            onChange={(value) => {
              templateLock['limit'] = true
              props.setAttributes({limit: parseInt(value) || getTemplateDefault('limit', parseInt)})
            }}
          />
          <ToggleControl
            label={__('Show Pagination ')}
            checked={props.attributes.pagination}
            onChange={(value) => {
              templateLock['pagination'] = true
              props.setAttributes({pagination: value})
            }}
          />
          <TextControl
            label={__('Page')}
            placeholder={__('1')}
            type={'number'}
            min={1}
            value={props.attributes.page || 1}
            onChange={(value) => {
              props.setAttributes({page: parseInt(value) || 1})
            }}
          />
        </PanelBody>
      </InspectorControls>
    </div>
  },

  /**
   * Called when Gutenberg "saves" the block to post_content
   */
  save: function (props) {
    return null
  }
})