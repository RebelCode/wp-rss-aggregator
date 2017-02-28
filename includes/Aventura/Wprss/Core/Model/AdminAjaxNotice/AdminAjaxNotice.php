<?php

namespace Aventura\Wprss\Core\Model\AdminAjaxNotice;

/**
 * Implementation of a notice that shows on the admin side and uses AJAX for dismissal.
 *
 * @since [*next-version*]
 */
class AdminAjaxNotice extends NoticeAbstract implements AdminAjaxNoticeInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getElementClass()
    {
        return $this->getData('element_class', '');
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getCloseButtonId()
    {
        return $this->getData('btn_close_id', null);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getCloseButtonClass()
    {
        return $this->getData('btn_close_class', 'btn-close');
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getCloseButtonContent()
    {
        return $this->getData('btn_close_content', '');
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getNonce()
    {
        return $this->getData('nonce', null);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getNonceElementId()
    {
        return $this->getData('nonce_element_id', null);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getNonceElementClass()
    {
        return $this->getData('nonce_element_class', 'admin-notice-nonce');
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getConditionOnError()
    {
        return $this->getData('condition_on_error', static::CONDITION_ON_ERROR_THROW_EXCEPTION);
    }
}
