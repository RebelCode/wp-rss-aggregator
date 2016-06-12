<?php

namespace Aventura\Wprss\Core\Http\Message\Ajax;

use Aventura\Wprss\Core;
use Aventura\Wprss\Core\Http\Message;

/**
 * @since [*next-version*]
 */
abstract class AbstractResponse extends Message\AbstractResponse implements Core\Http\Message\Ajax\AjaxInterface
{
    /** @since [*next-version*] */
    protected $ajaxData;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     * @see Core\DataObject::getData()
     * @since [*next-version*]
     */
    public function getAjaxData($key = null) {
        $this->_getAjaxData()->getData($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     * @see Core\DataObject::addData()
     * @param type $key
     * @param type $value
     * @return \Aventura\Wprss\Core\Http\Message\Ajax\AbstractResponse
     */
    public function setAjaxData($key, $value = null) {
        $data = $this->_getAjaxData();
        is_array($key)
                ? $data->addData($key)
                : $data->setData($key, $value);
        return $this;
    }

    /**
     * @since [*next-version*]
     * @return Core\DataObject
     */
    protected function _getAjaxData()
    {
        if (is_null($this->ajaxData)) {
            $this->ajaxData = new Core\DataObject();
        }

        return $this->ajaxData;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     * @return string
     */
    public function getBody()
    {
        return ($body = $this->getData(static::K_BODY))
                ? $body
                : static::convertToJson($this->_getAjaxData());
    }

    /**
     * Converts data in the passed object to a JSON representation.
     *
     * If a data object instance is used, will convert the object's data.
     * If any other type, will convert the properties.
     * If array, the array keys and values will represent the data.
     *
     * @since [*next-version*]
     * @param array|Core|Core\DataObjectInterface|object $object An object with data to convert.
     * @return string The JSON-encoded data.
     */
    public static function convertToJson($object)
    {
        $object = $object instanceof Core\DataObjectInterface
                ? $object->getData()
                : (array) $object;
        $json = json_encode($object);

        return $json;
    }
}
