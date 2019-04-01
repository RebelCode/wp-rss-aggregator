class Collection {
  constructor (data, primaryField = 'id') {
    this.data = data
    this.primaryField = primaryField
    return this
  }

  /**
   * Find element by params
   *
   * @param params
   * @param _default
   * @returns {*}
   */
  find (params, _default = null) {
    if (typeof params !== 'object' && params !== null) {
      params = {
        [this.primaryField]: params
      }
    }
    for (let i in this.data) {
      if (this._isMatching(this.data[i], params)) {
        return this.data[i]
      }
    }

    return _default
  }

  /**
   * Get and left only one column
   *
   * @param field
   * @returns {*}
   */
  pluck (field) {
    return this.data.map((item) => {
      return item[field]
    })
  }

  /**
   * Remove all elements matching given params
   *
   * @param params
   */
  remove (params) {
    for (let i in this.data) {
      if (this._isMatching(this.data[i], params)) {
        this.data.splice(i, 1)
      }
    }
    return this
  }

  /**
   * Add data from parameter array that not in
   * collection;
   *
   * @param data
   */
  appendDiff (data) {
    for (let el of data) {
      if (!this.contains(el)) {
        this.data.push(el)
      }
    }
  }

  /**
   * Add data from parameter array that not in
   * collection;
   *
   * @param data
   */
  prependDiff (data) {
    for (let el of data.slice().reverse()) {
      if (!this.contains(el)) {
        this.data.unshift(el)
      }
    }
  }

  contains (element) {
    for (let el of this.data) {
      if (el['id'] == element['id'])
        return true
    }
    return false
  }

  filterValues (callback) {
    return Object.keys(this.data).filter(key => {
      return callback(this.data[key], key)
    }).reduce((filteredResult, key) => {
      filteredResult[key] = this.data[key]
      return filteredResult
    }, {})
  }

  filter (params) {
    return this.data.filter((item) => {
      return this._isMatching(item, params)
    })
  }

  /**
   * Select all items where value of column in given array
   *
   * @param data
   * @param key
   * @returns {Array}
   */
  whereIn (data, key = 'id') {
    let newCollection = [],
      param = {}

    param[key] = null

    for (let val of data) {
      param[key] = val

      let item = this.find(param)
      if (item) {
        newCollection.push(item)
      }
    }

    return newCollection
  }

  key (field) {
    const data = this.data.slice().reduce((obj, item) => {
      obj[item[field]] = item
      return obj
    }, {})
    return new Collection(data)
  }

  mapValues (callback) {
    Object.keys(this.data).map((key) => {
      this.data[key] = callback(this.data[key], key)
    })
    return this
  }

  values () {
    return this.data
  }

  /**
   * Check element is matching params.
   *
   * @param element
   * @param params
   * @return {boolean}
   * @private
   */
  _isMatching (element, params) {
    if (!(element instanceof Object) && !(params instanceof Object)) {
      return element == params
    }

    let match = true
    for (let key of Object.keys(params)) {
      let keyMatch = element.hasOwnProperty(key) && (element[key] == params[key])
      match = match && (keyMatch)
    }
    return match
  }

}

function collect (data) {
  return new Collection(data)
}

export default collect
