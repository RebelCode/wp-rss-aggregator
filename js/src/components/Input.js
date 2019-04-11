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
      if (this.type === 'checkbox') {
        return <input type="checkbox"
                      checked={!!this.value}
                      onChange={() => this.$emit('input', !this.value)}
                      placeholder={this.placeholder}
                      {...{attrs: this.$attrs}}
        />
      }

      if (this.type !== 'select') {
        return <input type={this.type}
                      value={this.value}
                      onInput={(e) => this.$emit('input', e.target.value)}
                      placeholder={this.placeholder}
                      {...{attrs: this.$attrs}}
               />
      }
      return this.selectNode()
    },

    selectNode () {
      let options = Object.keys(this.options)
        .map(key => <option value={key} selected={ this.value === key }>{ this.options[key] }</option>)

      return <select
        {...{attrs: this.$attrs}}
        onChange={(e) => this.$emit('input', e.target.value)}
      >{ options }</select>
    },
  },
  render () {
    return <div class={{'form-input': true, 'form-input--disabled': this.$attrs.disabled || false}}>
      { this.label ? (
        <label class="form-input__label">
          <div>{this.label}</div>
          {this.description ? <div class="form-input__label-description">{this.description}</div> : ''}
        </label>
      ) : null }
      <div class="form-input__field">
        { this.inputNode() }
      </div>
    </div>
  }
}
