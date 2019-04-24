/**
 * Library agnostic wrapper for notifications.
 *
 * @since [*next-version*]
 *
 * @class NotificationsCenter
 */
export default class NotificationsCenter {
  /**
   * NotificationsCenter constructor.
   *
   * @since [*next-version*]
   *
   * @param {Function} show Function implementation for displaying messages.
   * @param {Function} error Function implementation for displaying errors.
   */
  constructor (show, error) {
    this.showMethod = show
    this.errorMethod = error
  }

  /**
   * Display informational message.
   *
   * @since [*next-version*]
   *
   * @param {string} msg Message for displaying
   * @param {object} options Options for notification.
   */
  show (msg, options = {}) {
    this.showMethod(msg, options)
  }

  /**
   * Display error message.
   *
   * @since [*next-version*]
   *
   * @param {string} msg Message for displaying
   * @param {object} options Options for notification.
   */
  error (msg, options = {}) {
    this.errorMethod(msg, options)
  }
}
