<?php

namespace RebelCode\Wpra\Core\Templates\Feeds\Models;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use InvalidArgumentException;
use OutOfRangeException;
use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\CompositeDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\MaskingDataSet;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpArrayOptionDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpPostArrayMetaDataSet;
use RebelCode\Wpra\Core\Templates\Feeds\Types\ListTemplateType;
use WP_Post;

/**
 * A specialized feed template model implementation that uses the old WP RSS Aggregator display settings as the
 * template options.
 *
 * @since 4.13
 */
class BuiltInFeedTemplate extends WpPostFeedTemplate
{
    /* @since 4.13 */
    use NormalizeIterableCapableTrait;

    /* @since 4.13 */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since 4.13 */
    use StringTranslatingTrait;

    /**
     * The name of the option from which to retrieve the template settings.
     *
     * @since 4.13
     */
    const WP_OPTION_NAME = 'wprss_settings_general';

    /**
     * The key to which to map the settings options.
     *
     * @since 4.13
     */
    const OPTIONS_KEY = 'options';

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param int|string|WP_Post $postOrId The post instance or ID.
     */
    public function __construct($postOrId)
    {
        parent::__construct($postOrId);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function createMetaDataSet($postOrId)
    {
        // Create the settings options dataset
        $settingsOpts = $this->createSettingsOptionsDataSet();
        // Get the data set for the posts's template "options" meta key
        $metaOpts = new WpPostArrayMetaDataSet($postOrId, static::META_PREFIX . static::OPTIONS_KEY);
        // Merge the meta and settings options with a composite data set.
        // Data reading prioritizes meta data. If not found, an option will be read from the settings.
        // Writing is done on both data sets. Iteration is unique.
        $fullOpts = new CompositeDataSet([$metaOpts, $settingsOpts]);

        // Retrieve the full original template meta
        $fullPostMeta = parent::createMetaDataSet($postOrId);
        // Create a dataset specifically for the template options
        $overrideOpts = new ArrayDataSet([
            static::OPTIONS_KEY => $fullOpts,
        ]);
        // Override the original meta's `options` with the combined Settings-Meta dataset
        $final = new MergedDataSet($fullPostMeta, $overrideOpts, [static::OPTIONS_KEY => true]);

        return $final;
    }

    /**
     * Creates the dataset for the template options that are saved in the WPRA general settings.
     *
     * @since 4.13
     *
     * @return DataSetInterface
     */
    protected function createSettingsOptionsDataSet()
    {
        // Get the default options as a static array dataset
        $defaultOpts = new ArrayDataSet(wprss_get_default_settings_general());
        // Get the saved settings options
        $savedOpts = new WpArrayOptionDataSet(static::WP_OPTION_NAME);
        // Merge the defaults and the saved options
        $allOpts = new MergedDataSet($savedOpts, $defaultOpts);
        // Alias the old settings options to the new template option names
        $aliasedOpts = new AliasingDataSet($allOpts, $this->getSettingsOptionsAliases());
        // Mask the options to hide the other non-template general settings
        $maskedOpts = new MaskingDataSet($aliasedOpts, $this->getSettingsOptionsMask(), false);

        return $maskedOpts;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function set($key, $value)
    {
        if ($key !== self::OPTIONS_KEY) {
            parent::set($key, $value);

            return;
        }

        try {
            $iterable = $this->_normalizeIterable($value);
            // If the key is the options key and the value is a collection, override the default behavior to set
            // each collection entry individually to the template options dataset
            $dataset = $this->get(self::OPTIONS_KEY);
            foreach ($iterable as $subKey => $subVal) {
                $dataset[$subKey] = $subVal;
            }
        } catch (InvalidArgumentException $exception) {
            throw new OutOfRangeException('Cannot set non-collection values to "options"');
        }
    }

    /**
     * Retrieves the settings options aliases.
     *
     * @since 4.13
     *
     * @return string[]
     */
    protected function getSettingsOptionsAliases()
    {
        return [
            'limit' => 'feed_limit',
            'title_max_length' => 'title_limit',
            'title_is_link' => 'title_link',
            'pagination_type' => 'pagination',
            'source_enabled' => 'source_enable',
            'source_prefix' => 'text_preceding_source',
            'source_is_link' => 'source_link',
            'author_enabled' => 'authors_enable',
            'date_enabled' => 'date_enable',
            'date_prefix' => 'text_preceding_date',
            'date_format' => 'date_format',
            'date_use_time_ago' => 'time_ago_format_enable',
            'links_behavior' => 'open_dd',
            'links_nofollow' => 'follow_dd',
            'links_video_embed_page' => 'video_link',
        ];
    }

    /**
     * Retrieves the mask for which settings option keys to retain in the dataset.
     *
     * @since 4.13
     *
     * @return bool[]
     */
    protected function getSettingsOptionsMask()
    {
        // Dummy list template type, used to get the options
        $listType = new ListTemplateType(new ArrayDataSet([]));
        $listOptions = $listType->getOptions();

        // Use a "true" mask for every list template type option available
        $mask = [];
        foreach ($listOptions as $key => $schema) {
            $mask[$key] = true;
        }

        return $mask;
    }
}
