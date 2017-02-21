<?php

namespace Aventura\Wprss\Core\Model\AdminAjaxNotice;

use \Aventura\Wprss\Core\Block;

/**
 * Implementation of a notice that shows on the admin side and uses AJAX for dismissal.
 *
 * @since [*next-version*]
 */
class AdminAjaxNotice extends NoticeAbstract
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
    public function getElementClass()
    {
        return $this->getData('element_class', '');
    }

    /**
     * Gets the HTML ID of the close button.
     *
     * @since [*next-version*]
     *
     * @return string The HTML ID attribute value string.
     */
    public function getCloseButtonId()
    {
        return $this->getData('btn_close_id', null);
    }

    /**
     * Gets the HTML class of the close button.
     *
     * @since [*next-version*]
     *
     * @return string The HTML class attribute value string.
     */
    public function getCloseButtonClass()
    {
        return $this->getData('btn_close_class', 'btn-close');
    }

    /**
     * Gets the content of the close button.
     *
     * @since [*next-version*]
     *
     * @return Block\BlockInterface|string The block or string for the close button content.
     */
    public function getCloseButtonContent()
    {
        return $this->getData('btn_close_content', '');
    }

    /**
     * Gets the AJAX nonce code.
     *
     * @since [*next-version*]
     *
     * @return type
     */
    public function getNonce()
    {
        return $this->getData('nonce', null);
    }

    /**
     * Gets the AJAX nonce HTML element ID.
     *
     * @since [*next-version*]
     *
     * @return type
     */
    public function getNonceElementId()
    {
        return $this->getData('nonce_element_id', null);
    }

    /**
     * Gets the AJAX nonce HTML element class.
     *
     * @return type
     */
    public function getNonceElementClass()
    {
        return $this->getData('nonce_element_class', 'admin-notice-nonce');
    }

    /**
     * Gets the action to be taken when an error is encountered during condition resolution.
     *
     * @see CONDITION_ON_ERROR_THROW_EXCEPTION
     *
     * @since [*next-version*]
     *
     * @return string A string identifying the action to be taken.
     */
    public function getConditionOnError()
    {
        return $this->getData('condition_on_error', static::CONDITION_ON_ERROR_THROW_EXCEPTION);
    }
}
