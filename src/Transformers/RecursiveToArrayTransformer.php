<?php

namespace RebelCode\Wpra\Core\Transformers;

use Dhii\Transformer\TransformerInterface;
use stdClass;
use Traversable;

class RecursiveToArrayTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        if (!$this->isIterable($source)) {
            return $source;
        }

        return $this->iterableToArray($source);
    }

    protected function iterableToArray($input)
    {
        $output = [];

        foreach ($input as $key => $value) {
            $output[$key] = $this->isIterable($value)
                ? $this->iterableToArray($value)
                : $value;
        }

        return $output;
    }

    protected function isIterable($value)
    {
        return is_array($value) || $value instanceof stdClass || $value instanceof Traversable;
    }
}
