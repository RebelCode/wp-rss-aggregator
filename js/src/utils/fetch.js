/**
 * Make fetch request and parse response JSON.
 *
 * @param url
 * @param data
 * @param config
 *
 * @return {Promise<Response>}
 */
export function post (url, data, config = {}) {
  const preparedData = new FormData()
  for (let key of Object.keys(data)) {
    preparedData.set(key, data[key])
  }
  return fetch(url, {
    method: 'post',
    body: preparedData
  }).then(response => {
    return response.json()
  }).then(data => {
    if (data.status !== 200) {
      throw data
    }
    return data
  })
}