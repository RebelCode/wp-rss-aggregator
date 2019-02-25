<template>
    <div class="form">
        <div class="form-group"
             v-for="datum of form"
             v-if="satisfiesCondition(datum)"
        >
            <template v-if="datum.type === 'radio'">
                <label :for="datum.name" v-html="datum.label" v-if="datum.label"></label>
                <div class="form-check" v-for="(radio, $i) of datum.options">
                    <input type="radio"
                           :name="datum.name"
                           :id="datum.name + '_' + $i"
                           :value="radio.value"
                           v-model="model[datum.name]"
                    >
                    <label :for="datum.name + '_' + $i">
                        {{ radio.label || radio.value }}
                    </label>
                </div>
            </template>

            <template v-if="datum.type === 'textarea'">
                <label :for="datum.name" v-html="datum.label" v-if="datum.label"></label>
                <textarea v-model="model[datum.name]" :id="datum.name"></textarea>
            </template>

            <template v-if="datum.type === 'content'">
                <div :class="datum.className">
                    <p v-html="datum.label"></p>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
  export default {
    props: {
      /*
       * Form, described by object containing information
       * about each field.
       */
      form: {
        type: Array,
      },

      /*
       * Form model.
       */
      value: {
        type: Object,
      }
    },
    computed: {
      model: {
        get () {
          return this.value
        },
        set (value) {
          this.$emit('input', value)
        }
      }
    },
    methods: {
      satisfiesCondition (datum) {
        if (!datum.condition) {
          return true
        }
        let compareFunction = this.getConditionFunction(datum.condition.operator);
        if (!compareFunction) {
          return false
        }
        return compareFunction(datum.condition.field, datum.condition.value)
      },

      getConditionFunction (operator) {
        const fns = {
          '=': (field, value) => {
            return this.model[field] === value
          }
        }
        if (!fns[operator]) {
          return null
        }
        return fns[operator]
      }
    }
  }
</script>
