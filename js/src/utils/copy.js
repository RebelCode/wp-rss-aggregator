/**
 * Helper functions to copy text to clipboard.
 *
 * @see https://stackoverflow.com/questions/400212/how-do-i-copy-to-the-clipboard-in-javascript
 *
 * @param text
 */

const fallbackCopyToClipboard = function (text, scrollContainer = null) {
  scrollContainer = scrollContainer || document.body.parentElement
  var textArea = document.createElement('textarea')
  textArea.value = text

  var currentScrollTop = scrollContainer.scrollTop

  document.body.appendChild(textArea)
  textArea.focus()
  textArea.select()

  try {
    var successful = document.execCommand('copy')
    var msg = successful ? 'successful' : 'unsuccessful'
    console.log('Fallback: Copying text command was ' + msg)
  } catch (err) {
    console.error('Fallback: Oops, unable to copy', err)
  }

  document.body.removeChild(textArea)

  scrollContainer.scrollTop = currentScrollTop
}

export function copyToClipboard (text, scrollContainer = null) {
  if (!navigator.clipboard) {
    fallbackCopyToClipboard(text, scrollContainer)
    return
  }
  navigator.clipboard.writeText(text).then(function () {
    console.log('Async: Copying to clipboard was successful!')
  }, function (err) {
    console.error('Async: Could not copy text: ', err)
  })
}
