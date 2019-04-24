export default {
  props: {
    id: {
      type: String,
      default () {
        return Math.random().toString(36).substr(0, 12)
      }
    },
    label: {},
    description: {},
    type: {},
    value: {},
    placeholder: {},
    title: {},
    inputDisabled: {},
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
                      id={this.id}
                      checked={!!this.value}
                      onChange={() => this.$emit('input', !this.value)}
                      placeholder={this.placeholder}
                      disabled={this.$attrs.disabled || this.inputDisabled}
                      {...{attrs: this.$attrs}}
        />
      }

      if (this.type !== 'select') {
        return <input type={this.type}
                      value={this.value}
                      id={this.id}
                      onInput={(e) => this.$emit('input', e.target.value)}
                      placeholder={this.placeholder}
                      disabled={this.$attrs.disabled || this.inputDisabled}
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
        id={this.id}
        disabled={this.$attrs.disabled || this.inputDisabled}
        onChange={(e) => this.$emit('input', e.target.value)}
      >{ options }</select>
    },
  },
  render () {
    let directives = []

    if (this.title) {
      directives.push({
        name: 'tippy',
      })
    }

    return <div class={{'form-input': true, 'form-input--disabled': this.$attrs.disabled || false}}>
      { this.label ? (
        <label class="form-input__label" for={this.id}>
          <div>
            {this.label}
            {
              this.title ? (
                <div class="form-input__tip" {...{directives}} title={this.title}>
                  <span class="dashicons dashicons-editor-help"/>
                </div>
              ) : null
            }
          </div>
          {this.description ? <div class="form-input__label-description" {...{domProps: {innerHTML: this.description}}}/> : ''}
        </label>
      ) : null }
      <div class="form-input__field">
        { this.inputNode() }
      </div>
    </div>
  }
}
