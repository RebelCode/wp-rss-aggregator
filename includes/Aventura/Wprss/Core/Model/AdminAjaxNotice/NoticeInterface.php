<?php

namespace Aventura\Wprss\Core\Model\AdminAjaxNotice;

/**
 * Something that represents a notice shown on the admin WordPress screens.
 *
 * @since [*next-version*]
 */
interface NoticeInterface
{
    /**
     * Condition type that signifies that all conditions in a collection must evaluate to true.
     *
     * @since [*next-version*]
     */
    const CONDITION_TYPE_ALL = 'all';

    /**
     * Condition type that signifies that at least one condition in a collection must be evaluate
     * to true.
     *
     * @since [*next-version*]
     */
    const CONDITION_TYPE_ANY = 'any';

    /**
     * Condition type that signifies that none of the conditions in a collection must evaluate
     * to true.
     *
     * @since [*next-version*]
     */
    const CONDITION_TYPE_NONE = 'none';

    /**
     * Condition type that signifies that at least on of the conditions in a collection must
     * evaluate to false.
     *
     * @since [*next-version*]
     */
    const CONDITION_TYPE_ALMOST = 'almost';

    /**
     * A notice type that represents a successful operation.
     *
     * @since [*next-version*]
     */
    const TYPE_SUCCESS = 'success';

    /**
     * A notice type that signifies that something has been updated.
     *
     * @since [*next-version*]
     */
    const TYPE_UPDATED = 'updated';

    /**
     * A notice type that represents an informational notice message.
     *
     * @since [*next-version*]
     */
    const TYPE_INFO = 'info';

    /**
     * A notice type that represents a warning message.
     *
     * @since [*next-version*]
     */
    const TYPE_WARNING = 'warning';

    /**
     * A notice type that represents an error message.
     *
     * @since [*next-version*]
     */
    const TYPE_ERROR = 'error';

    /**
     * The normal styling mode for notices.
     *
     * @since [*next-version*]
     */
    const STYLE_NORMAL = 'normal';

    /**
     * The alternative styling mode for notices.
     *
     * @since [*next-version*]
     */
    const STYLE_ALT = 'alt';

    /**
     * Gets the ID of the notice.
     *
     * @since [*next-version*]
     *
     * @return string The notice ID string.
     */
    public function getId();

    /**
     * Checks if the notice is active or not.
     *
     * i.e. If it can be displayed or not.
     *
     * @since [*next-version*]
     *
     * @return bool True if the notice is active, false if not.
     */
    public function isActive();

    /**
     * Gets the notice type.
     *
     * @see TYPE_SUCCESS
     * @see TYPE_UPDATED
     * @see TYPE_INFO
     * @see TYPE_WARNING
     * @see TYPE_ERROR
     *
     * @since [*next-version*]
     *
     * @return string The notice type string.
     */
    public function getType();

    /**
     * Gets the notice style.
     *
     * @see STYLE_NORMAL
     * @see STYLE_ALT
     *
     * @since [*next-version*]
     *
     * @return string The notice style type string.
     */
    public function getStyle();

    /**
     * Gets the notice content.
     *
     * @since [*next-version*]
     *
     * @return BlockInterface|string The notice content block or string.
     */
    public function getContent();

    /**
     * Gets the conditions which dictate when the notice is shown.
     *
     * @since [*next-version*]
     *
     * @return array An array of callback function conditions.
     */
    public function getConditions();

    /**
     * Gets the condition resolution type.
     *
     * @see CONDITION_TYPE_ALL
     * @see CONDITION_TYPE_ANY
     * @see CONDITION_TYPE_NONE
     * @see CONDITION_TYPE_ALMOST
     *
     * @since [*next-version*]
     *
     * @return string The condition type string.
     */
    public function getConditionType();

    /**
     * Gets whether the notice is dismissable or persistent.
     *
     * @since [*next-version*]
     *
     * @return bool True if the notice can be dismissed, false if it is persistent.
     */
    public function isDismissable();
}
