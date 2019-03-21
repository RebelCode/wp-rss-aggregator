<?php

namespace RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates;

use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;
use RebelCode\Wpra\Core\RestApi\EndPoints\AbstractRestApiEndPoint;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The REST API end point for retrieving templates.
 *
 * @since [*next-version*]
 */
class GetTemplatesEndPoint extends AbstractRestApiEndPoint
{
    /**
     * The query iterator for templates.
     *
     * @since [*next-version*]
     *
     * @var CollectionInterface
     */
    protected $collection;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param CollectionInterface $collection The templates' collection data set.
     */
    public function __construct(CollectionInterface $collection)
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

        $rSearch = isset($request['s']) ? $request['s'] : null;
        $fSearch = filter_var($rSearch, FILTER_SANITIZE_STRING);

        $data = $this->getDataSet($fId, $fSearch);

        return new WP_REST_Response($data);
    }

    protected function getDataSet($id, $search)
    {
        if (!empty($id)) {
            return $this->collection[$id];
        }

        if (!empty($search)) {
            return $this->collection->search($search);
        }

        return $this->collection;
    }
}
