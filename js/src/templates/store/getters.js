import collect from '../Collection'

export default {
  item: state => id => {
    return collect(state.items).find(id)
  }
}
