export default {
  props: {
    label: {},
    description: {},
    type: {},
    value: {},
    placeholder: {},
    options: {
      default () {
        return {}
      }
    },
  },
  methods: {
    inputNode () {
      if (this.type !== 'select') {
        return <input type={this.type}
                      value={this.value}
                      onInput={(e) => this.$emit('input', e.target.value)}
                      placeholder={this.placeholder}
               />
      }
      return this.selectNode()
    },

    selectNode () {
      let options = Object.keys(this.options)
        .map(key => <option value={key} selected={ this.value === key }>{ this.options[key] }</option>)

      return <select onChange={(e) => this.$emit('input', e.target.value)}>
        { options }
      </select>
    },
  },
  render () {
    return <div class="form-input">
      <label for="">
        <div>{ this.label }</div>
        { this.description ? <div>{ this.description }</div> : '' }
      </label>
      { this.inputNode() }
    </div>
  }
}
