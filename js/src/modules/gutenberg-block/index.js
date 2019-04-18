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
      default: ''
    },
    pagination_enabled: {
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
          <SelectControl
            label={ __( 'Select Template' ) }
            value={ props.attributes.template }
            onChange={(template) => {
              props.setAttributes({template: template || ''})
            }}
            options={WPRA_BLOCK.templates}
          />
          <TextControl
            label={__('Feed Limit')}
            help={__('Number of feed items to display')}
            placeholder={__('15')}
            type={'number'}
            min={1}
            value={props.attributes.limit || 15}
            onChange={(value) => {
              props.setAttributes({limit: parseInt(value) || 15})
            }}
          />
          <ToggleControl
            label={__('Show Pagination ')}
            checked={props.attributes.pagination_enabled}
            onChange={(value) => {
              props.setAttributes({pagination_enabled: value})
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