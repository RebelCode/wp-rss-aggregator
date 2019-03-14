<?php

namespace RebelCode\Wpra\Core\Templates;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\CreateTemplateRenderExceptionCapableTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Exception;
use InvalidArgumentException;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Templates\Types\TemplateTypeInterface;
use WP_Post;

/**
 * An implementation of a standard Dhii template that, depending on context, delegates rendering to a WP RSS
 * Aggregator feeds template.
 *
 * @since [*next-version*]
 */
class MasterFeedsTemplate implements TemplateInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateTemplateRenderExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The ID of the template to use by default.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $default;

    /**
     * An associative array of template type instances.
     *
     * @since [*next-version*]
     *
     * @var TemplateTypeInterface[]
     */
    protected $types;

    /**
     * The collection of templates.
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
     * @param string           $default    The name of the template to use by default.
     * @param DataSetInterface $collection The collection of templates.
     */
    public function __construct($default, DataSetInterface $collection)
    {
        $this->types = [];
        $this->default = $default;
        $this->collection = $collection;
    }

    /**
     * Registers a new feed template type.
     *
     * @since [*next-version*]
     *
     * @param TemplateTypeInterface $templateType The feed template type instance.
     */
    public function addTemplateType(TemplateTypeInterface $templateType)
    {
        $this->types[$templateType->getKey()] = $templateType;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render($ctx = null)
    {
        try {
            $arrCtx = $this->_normalizeArray($ctx);
        } catch (InvalidArgumentException $exception) {
            $arrCtx = [];
        }

        $hasTemplate = array_key_exists('template', $arrCtx);

        // If not template is manually specified, check if should default to legacy rendering system
        if (!$hasTemplate && $this->fallBackToLegacySystem()) {
            return wprss_display_feed_items();
        }

        $templateKey = $hasTemplate
            ? $arrCtx['template']
            : $this->default;

        try {
            $model = $this->collection[$templateKey];
        } catch (Exception $exception) {
            $model = null;
        }

        if ($model === null) {
            throw $this->_createTemplateRenderException(
                sprintf(__('Template "%s" does not exist', WPRSS_TEXT_DOMAIN), $templateKey),
                null, null, $this, $ctx
            );
        }

        $type = isset($model['type']) ? $model['type'] : '';
        $template = $this->getTemplateType($type);

        $fullCtx = new MergedDataSet(new ArrayDataSet($arrCtx), $model);

        return $template->render($fullCtx);
    }

    /**
     * Retrieves a template model by key.
     *
     * @since [*next-version*]
     *
     * @param string $key The key of the template.
     *
     * @return DataSetInterface|null The template model instance or null if not found.
     */
    public function getTemplateModel($key)
    {
        $posts = get_posts([
            'post_type' => WPRSS_FEED_TEMPLATE_CPT,
            'posts_per_page' => 1,
            'name' => $key,
        ]);

        if (empty($posts)) {
            return null;
        }

        return wprss_create_template_from_post($posts[0]);
    }

    /**
     * Retrieves a template type by key.
     *
     * @since [*next-version*]
     *
     * @param $key
     *
     * @return mixed|TemplateTypeInterface|string
     */
    protected function getTemplateType($key)
    {
        return isset($this->types[$key])
            ? $this->types[$key]
            : $this->types['list'];
    }

    /**
     * Checks whether or not the master feeds template should fall back to the legacy rendering method when no
     * template is explicitly specified in the render context.
     *
     * @since [*next-version*]
     *
     * @return bool True to fall back to the legacy rendering system, false to use the default template.
     */
    protected function fallBackToLegacySystem()
    {
        return apply_filters('wpra/templates/fallback_to_legacy_system', false);
    }
}
