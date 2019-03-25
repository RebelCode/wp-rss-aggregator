import collect from 'app/utils/Collection'

export default {
  item: state => id => {
    return collect(state.items).find(id)
  }
}
