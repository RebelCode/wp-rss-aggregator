<?php

namespace Aventura\Wprss\Core\Model\AdminAjaxNotice;

use \Aventura\Wprss\Core;

/**
 * Basic functionality for a notice.
 *
 * @since [*next-version*]
 */
abstract class NoticeAbstract extends Core\Model\ModelAbstract implements NoticeInterface
{
    protected static $dismissModes = array(
        NoticeInterface::DISMISS_MODE_NONE          => NoticeInterface::DISMISS_MODE_NONE,
        NoticeInterface::DISMISS_MODE_FRONTEND      => NoticeInterface::DISMISS_MODE_FRONTEND,
        NoticeInterface::DISMISS_MODE_AJAX          => NoticeInterface::DISMISS_MODE_AJAX,
    );

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function isActive()
    {
        return (bool) $this->getData('active', true);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getType()
    {
        return $this->getData('type', static::TYPE_UPDATED);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getStyle()
    {
        return $this->getData('style', static::STYLE_NORMAL);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getContent()
    {
        return $this->getData('content', '');
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getConditions()
    {
        return $this->getData('condition', array());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getConditionType()
    {
        return $this->getData('condition_type', static::CONDITION_TYPE_ALL);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function isDismissable()
    {
        return $this->getDismissMode() !== NoticeInterface::DISMISS_MODE_NONE;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getDismissMode()
    {
        return $this->getData('dismiss_mode', NoticeInterface::DISMISS_MODE_AJAX);
    }
}
