export default {
  props: {
    label: {},
    description: {},
    type: {},
    value: {},
    placeholder: {},
    title: {},
    labelTitle: {},
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
    let directives = []

    if (this.title) {
      directives.push({
        name: 'tippy',
      })
    }

    return <div class={{'form-input': true, 'form-input--disabled': this.$attrs.disabled || false}}>
      { this.label ? (
        <label class="form-input__label">
          <div>
            {this.label}
            {
              this.title && this.labelTitle ? (
                <div class="form-input__tip" {...{directives}} title={this.title}>
                  <span class="dashicons dashicons-info"></span>
                </div>
              ) : null
            }
          </div>
          {this.description ? <div class="form-input__label-description">{this.description}</div> : ''}
        </label>
      ) : null }
      <div class="form-input__field">
        { this.inputNode() }
        {
          this.title && !this.labelTitle ? (
            <div class="form-input__tip" {...{directives}} title={this.title}>
              <span class="dashicons dashicons-info"></span>
            </div>
          ) : null
        }
      </div>
    </div>
  }
}
