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
        return $this->getData('conditions', array());
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
     * @return type
     */
    public function isDismissable()
    {
        return $this->getData('dismissable', true);
    }
}
