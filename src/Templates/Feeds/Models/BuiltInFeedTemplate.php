<?php

namespace RebelCode\Wpra\Core\Templates\Feeds\Models;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use InvalidArgumentException;
use OutOfRangeException;
use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\MaskingDataSet;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpArrayOptionDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpPostArrayMetaDataSet;
use WP_Post;

/**
 * A specialized feed template model implementation that uses the old WP RSS Aggregator display settings as the
 * template options.
 *
 * @since [*next-version*]
 */
class BuiltInFeedTemplate extends WpPostFeedTemplate
{
    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The name of the option from which to retrieve the template settings.
     *
     * @since [*next-version*]
     */
    const WP_OPTION_NAME = 'wprss_settings_general';

    /**
     * The key to which to map the DB options.
     *
     * @since [*next-version*]
     */
    const OPTIONS_KEY = 'options';

    /**
     * Constructor.
     *
     * @since [*next-version*]
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
     * @since [*next-version*]
     */
    protected function createMetaDataSet($postOrId)
    {
        // Create the DB options dataset
        $dbOpts = $this->createDbOptionsDataSet();
        // Get the data set for the posts's template options meta data
        $metaOpts = new WpPostArrayMetaDataSet($postOrId, static::META_PREFIX . static::OPTIONS_KEY);

        // Merge the DB options and the meta options.
        // Any options not in the old general settings will be retrieved from meta
        // Any non-general-settings options that are set will be saved in meta
        $fullOptions = new MergedDataSet($dbOpts, $metaOpts);

        // Retrieve the full original template meta
        $originalMeta = parent::createMetaDataSet($postOrId);
        // Create a dataset specifically for the template options
        $overrideOpts = new ArrayDataSet([
            static::OPTIONS_KEY => $fullOptions
        ]);
        // Override the original meta's `options` with the combined DB-Meta dataset
        $final = new MergedDataSet($originalMeta, $overrideOpts, [static::OPTIONS_KEY => true]);

        return $final;
    }

    /**
     * Creates the dataset for the template options that are saved in the WPRA general settings.
     *
     * @since [*next-version*]
     *
     * @return DataSetInterface
     */
    protected function createDbOptionsDataSet()
    {
        // Get the default options as a static array dataset
        $defaultOpts = new ArrayDataSet(wprss_get_default_settings_general());
        // Get the saved DB options
        $savedOpts = new WpArrayOptionDataSet(static::WP_OPTION_NAME);
        // Merge the defaults and the saved options
        $allOpts = new MergedDataSet($savedOpts, $defaultOpts);
        // Mask the options to hide the other non-template general settings
        $maskedOpts = new MaskingDataSet($allOpts, $this->getDbOptionsMask(), false);
        // Alias the old DB options to the new template option names
        $aliasedDbOpts = new AliasingDataSet($maskedOpts, $this->getDbOptionsAliases());

        return $aliasedDbOpts;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
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
     * Retrieves the DB options aliases.
     *
     * @since [*next-version*]
     *
     * @return string[]
     */
    protected function getDbOptionsAliases()
    {
        return [
            'items_max_num' => 'feed_limit',
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
            'links_open_behavior' => 'open_dd',
            'links_rel_nofollow' => 'follow_dd',
            'links_video_embed_page' => 'video_link',
        ];
    }

    /**
     * Retrieves the mask for which DB option keys to retain in the dataset.
     *
     * @since [*next-version*]
     *
     * @return bool[]
     */
    protected function getDbOptionsMask()
    {
        return [
            'feed_limit' => true,
            'title_limit' => true,
            'title_link' => true,
            'pagination' => true,
            'source_enable' => true,
            'source_link' => true,
            'text_preceding_source' => true,
            'authors_enable' => true,
            'date_enable' => true,
            'text_preceding_date' => true,
            'date_format' => true,
            'time_ago_format_enable' => true,
            'open_dd' => true,
            'follow_dd' => true,
            'video_link' => true,
        ];
    }
}
