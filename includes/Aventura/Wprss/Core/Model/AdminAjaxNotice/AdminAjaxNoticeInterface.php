<?php

namespace Aventura\Wprss\Core\Model\AdminAjaxNotice;

use \Aventura\Wprss\Core\Block\BlockInterface;

/**
 * Something that represents a WPRA admin AJAX notice.
 *
 * @since [*next-version*]
 */
interface AdminAjaxNoticeInterface extends NoticeInterface
{
    /**
     * Throw an exception when an error is encountered during condition resolution.
     *
     * @since [*next-version*]
     */
    const CONDITION_ON_ERROR_THROW_EXCEPTION = 'throw_exception';

    /**
     * Gets the notice HTML element class.
     *
     * @since [*next-version*]
     *
     * @return string The HTML "class" attribute value.
     */
    public function getElementClass();

    /**
     * Gets the HTML ID of the close button.
     *
     * @since [*next-version*]
     *
     * @return string The HTML ID attribute value string.
     */
    public function getCloseButtonId();

    /**
     * Gets the HTML class of the close button.
     *
     * @since [*next-version*]
     *
     * @return string The HTML class attribute value string.
     */
    public function getCloseButtonClass();

    /**
     * Gets the content of the close button.
     *
     * @since [*next-version*]
     *
     * @return BlockInterface|string The block or string for the close button content.
     */
    public function getCloseButtonContent();

    /**
     * Gets the AJAX nonce code.
     *
     * @since [*next-version*]
     *
     * @return string The nonce code string.
     */
    public function getNonce();

    /**
     * Gets the AJAX nonce HTML element ID.
     *
     * @since [*next-version*]
     *
     * @return string The HTML ID attribute value string.
     */
    public function getNonceElementId();

    /**
     * Gets the AJAX nonce HTML element class.
     *
     * @return string The HTML class attribute value string.
     */
    public function getNonceElementClass();

    /**
     * Gets the action to be taken when an error is encountered during condition resolution.
     *
     * @see CONDITION_ON_ERROR_THROW_EXCEPTION
     *
     * @since [*next-version*]
     *
     * @return string A string identifying the action to be taken.
     */
    public function getConditionOnError();
}
