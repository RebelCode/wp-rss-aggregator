<?php

namespace RebelCode\Wpra\Core\RestApi\Templates;

use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\RestApi\AbstractRestApiHandler;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The REST API handler for retrieving templates.
 *
 * @since [*next-version*]
 */
class GetTemplatesHandler extends AbstractRestApiHandler
{
    /**
     * The query iterator for templates.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $collection;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface $collection The templates' collection data set.
     */
    public function __construct(DataSetInterface $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function handle(WP_REST_Request $request)
    {
        $rId = isset($request['id']) ? ($request['id']) : null;
        $fId = filter_var($rId, FILTER_SANITIZE_STRING);

        $data = ($fId === null)
            ? $this->collection
            : $this->collection[$fId];

        return new WP_REST_Response($data);
    }
}
