<?php

namespace RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates;

use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\RestApi\EndPoints\AbstractRestApiEndPoint;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The REST API endpoint for deleting templates.
 *
 * @since [*next-version*]
 */
class DeleteTemplateEndPoint extends AbstractRestApiEndPoint
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
        $fId = filter_var($rId, FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

        if (empty($fId) || !isset($this->collection[$fId])) {
            return new WP_Error(
                'template_not_found',
                sprintf(__('Template "%s" does not exist', 'wprss'), $fId),
                ['status' => 404]
            );
        }

        unset($this->collection[$fId]);

        return new WP_REST_Response([]);
    }
}
