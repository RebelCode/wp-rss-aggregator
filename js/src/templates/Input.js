export default {
  props: {
    label: {},
    description: {},
    type: {},
    value: {},
    options: {
      default () {
        return {}
      }
    },
  },
  methods: {
    inputNode () {
      if (this.type !== 'select') {
        return <input type={this.type} onInput={(e) => this.$emit('input', e.target.value)}/>
      }
      return this.selectNode()
    },

    selectNode () {
      let options = Object.keys(this.options)
        .map(key => <option value={key} selected={ this.value === key }>{ this.options[key] }</option>)

      return <select onSelect={(e) => this.$emit('input', e)}>
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
