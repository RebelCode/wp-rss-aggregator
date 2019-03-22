export default {
  set (state, items = []) {
    state.isInitialized = true
    state.items = items
  },

  updatePreset (state, preset = null) {
    state.preset = preset
  }
}
