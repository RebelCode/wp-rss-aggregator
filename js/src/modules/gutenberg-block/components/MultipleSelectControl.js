import { Component } from '@wordpress/element'
import {
  FormTokenField,
  Placeholder,
  Spinner,
  BaseControl,
} from '@wordpress/components'

export default class MultipleSelectControl extends Component {
  constructor (props) {
    super(...arguments)
    this.props = props
    this.state = {
      tokens: [],
      loading: false,
      items: []
    }
  }

  componentDidMount () {
    this.setState({loading: true})

    jQuery.post(WPRA_BLOCK.ajax_url, {
      action: 'wprss_fetch_items',
    }, (data) => {
      data = JSON.parse(data)
      this.setState({
        loading: false,
        items: data.items
      })
    })
  }

  render () {
    const setState = this.setState.bind(this)
    const onChange = this.props.onChange
    const items = this.state.items

    if (this.state.loading) {
      return <Placeholder>
        <Spinner/>
      </Placeholder>
    }
    return <BaseControl
      help={this.props.help || ''}
    >
      <FormTokenField
        label={this.props.label || ''}
        placeholder={this.props.placeholder || ''}
        value={this.props.value.map(function (id) {
          return items.find(item => item.value === id)
        }).filter(item => !!item)}
        suggestions={this.state.items.map(function (item) {
          item.toLocaleLowerCase = function () {
            return item.title.toLocaleLowerCase()
          }
          item.toString = function () {
            return item.title
          }
          return item
        })}
        displayTransform={function (item) {
          if ('number' === typeof item) {
            item = items.find(function (iteratedItem) {
              return iteratedItem.value === item
            })
          }
          if ('object' === typeof item) {
            return item.title
          }
          return item
        }}
        saveTransform={function (token) {
          return token
        }}
        onChange={function (tokens) {
          setState({tokens})
          onChange(tokens.map(function (item) {
            return item.value
          }))
        }}
      />
    </BaseControl>
  }
}