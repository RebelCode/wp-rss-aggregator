<?php

namespace RebelCode\Wpra\Core\Models;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use InvalidArgumentException;
use OutOfRangeException;
use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\MaskingDataSet;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\WpArrayOptionDataSet;
use RebelCode\Wpra\Core\Models\WpPostFeedTemplate;
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
        // Create the DB options dataset and merge it with the defaults
        $defaultOpts = new ArrayDataSet(wprss_get_default_settings_general());
        $savedOpts = new WpArrayOptionDataSet(static::WP_OPTION_NAME);
        $allOpts = new MergedDataSet($savedOpts, $defaultOpts);
        // Alias the mask the options dataset
        $aliasedDbOpts = new AliasingDataSet($allOpts, $this->getDbOptionsAliases());
        $maskedDbOpts = new MaskingDataSet($aliasedDbOpts, $this->getDbOptionsMask(), false);
        // Wrap the options dataset in another that maps it to an options key
        $options = new ArrayDataSet([
            static::OPTIONS_KEY => $maskedDbOpts
        ]);

        $originalMeta = parent::createMetaDataSet($postOrId);

        // Merge the original post meta and the options dataset, but explicitly let the options key be overridden
        return new MergedDataSet($originalMeta, $options, [static::OPTIONS_KEY => true]);
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
            'template_type' => true,
            'pagination_enabled' => true,
            'author_prefix' => true,
            'items_max_num' => true,
            'title_max_length' => true,
            'title_is_link' => true,
            'pagination_type' => true,
            'source_enabled' => true,
            'source_prefix' => true,
            'source_is_link' => true,
            'author_enabled' => true,
            'date_enabled' => true,
            'date_prefix' => true,
            'date_format' => true,
            'date_use_time_ago' => true,
            'links_open_behavior' => true,
            'links_rel_nofollow' => true,
            'links_video_embed_page' => true,
        ];
    }
}
