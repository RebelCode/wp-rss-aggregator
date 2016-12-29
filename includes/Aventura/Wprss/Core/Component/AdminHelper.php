<?php

namespace Aventura\Wprss\Core\Component;

use Aventura\Wprss\Core;

/**
 * Helper component for things related to the backend.
 *
 * @since 4.10
 */
class AdminHelper extends Core\Plugin\ComponentAbstract
{
    /**
     * Determine if currently showing page is related to WPRSS.
     *
     * @since 4.10
     *
     * @return bool True if currently showing a WPRSS-related page; false otherwise.
     */
    public function isWprssPage()
    {
        require_once(WPRSS_INC . 'functions.php');
        require_once(WPRSS_INC . 'admin-ajax-notice.php');

        return wprss_is_wprss_page();
    }

    /**
     * Creates a new instance of a Command.
     *
     * A command is a callable object that can contain all data necessary to invoke a callback.
     *
     * @since 4.10
     *
     * @param array|callable $data A callable, or an array with the follwing indices:
     *  - `function` - The callable to assign to the command;
     *  - `args` - An array of arguments to invoke the command with.
     *
     * @return Core\Model\Command
     */
    public function createCommand($data)
    {
        $cmd = new Core\Model\Command($data);

        return $cmd;
    }

    /**
     * Resolves a value to something concrete.
     *
     * If the value is a callable, calls it. Otherwise, returns value.
     *
     * @since 4.10
     *
     * @param mixed $value Anything.
     *
     * @return mixed A non-callable value.
     */
    public function resolveValue($value)
    {
        if (is_callable($value)) {
            $value = call_user_func_array($value, array());
        }

        return $value;
    }
}
