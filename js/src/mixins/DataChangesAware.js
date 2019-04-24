import jsonClone from 'app/utils/jsonClone'
import equal from 'fast-deep-equal'

/**
 * Mixing for functionality that allows to track down and revert changes made on
 * the primary editing data model.
 *
 * `model` property on base object is required for using this mixin.
 */
export default {
  data () {
    return {
      changes: {
        model: {},
      }
    }
  },
  methods: {
    /**
     * Whether the model has been changed after last model "remembering".
     *
     * @return {boolean}
     */
    isChanged () {
      return !equal(this.model, this.changes.model)
    },

    /**
     * Remember current data model, but without any references to main model
     * properties. It is important to clean any additional observers from object
     * otherwise "memorized" model clone will be changed when model get changed.
     */
    rememberModel () {
      this.$set(this.changes, 'model', jsonClone(this.model))
    },

    /**
     * Cancel any changes on main model object.
     */
    cancelChanges () {
      if (!confirm('Are you sure you want to cancel your changes for this template? This action cannot be reverted and all changes made since your last save will be lost.')) {
        return
      }
      this.$set(this, 'model', jsonClone(this.changes.model))
    },
  }
}
