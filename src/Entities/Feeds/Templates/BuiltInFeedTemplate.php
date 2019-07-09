<?php

namespace RebelCode\Wpra\Core\Entities\Feeds\Templates;

use RebelCode\Wpra\Core\Data\AbstractDelegateDataSet;
use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\DelegatorDataSet;
use RebelCode\Wpra\Core\Data\MaskingDataSet;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpCptDataSet;
use RebelCode\Wpra\Core\Data\Wp\WpPostArrayMetaDataSet;
use WP_Post;

/**
 * A specialized feed template model implementation that uses the old WP RSS Aggregator display settings as the
 * template options.
 *
 * @since 4.13
 */
class BuiltInFeedTemplate extends AbstractDelegateDataSet
{
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
     * The meta prefix.
     *
     * @since 4.14
     */
    const META_PREFIX = 'wprss_template_';

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param int|string|WP_Post $postOrId The post instance or ID.
     */
    public function __construct($postOrId)
    {
        parent::__construct($this->createDataSet($postOrId));
    }

    /**
     * Creates the internal data set.
     *
     * @since 4.14
     *
     * @param int|WP_Post $postOrId The post instance or ID.
     *
     * @return DataSetInterface
     */
    protected function createDataSet($postOrId)
    {
        // Create the CPT data set, which only includes the required wp_post fields, which are aliased
        $cptDataSet = new AliasingDataSet(
            new WpCptDataSet(
                $postOrId,
                static::META_PREFIX,
                ['ID', 'post_title', 'post_name']
            ),
            [
                'id' => 'ID',
                'name' => 'post_title',
                'slug' => 'post_name',
            ]
        );

        // Create the data set for the template options (the "options" key)
        {
            // Create the data set for the "options" sub-meta
            $metaOptsDataSet = new WpPostArrayMetaDataSet($postOrId, static::META_PREFIX . 'options');
            // Create the data set for the "options" stored in the settings
            // And wrap it to be able to use the aliases instead of the old settings keys
            // And mask it to prevent other settings from being read or written to
            $settingOptsDataSet = new MaskingDataSet(
                new AliasingDataSet(
                    wpra_container()->get('wpra/settings/general/dataset'),
                    $this->getSettingsAliases()
                ),
                $this->getSettingsMask(),
                false
            );

            // Create the template options delegator data set for the options in meta and settings
            // This is used to determine which keys are stored in which which data set
            $optsDelegatorDataSet = new DelegatorDataSet(
                [
                    'settings' => $settingOptsDataSet,
                    'meta' => $metaOptsDataSet,
                ],
                DelegatorDataSet::fixedMap($this->getKeyMapping(), 'meta')
            );
        }

        // Wrap the opts delegator data set to nest it under the "options" key, with recursive writing
        // This consists of the post data set, and the meta and settings combined under an "options" key
        $optsDataSet = new ArrayDataSet(
            [
                'options' => $optsDelegatorDataSet,
            ],
            true
        );

        // Create the full data set, explicitly forwarding the options key to the second data set
        $dataSet = new MergedDataSet(
            $cptDataSet,
            $optsDataSet,
            ['options' => true],
            MergedDataSet::ITERATE_BOTH
        );

        return $dataSet;
    }

    /**
     * Retrieves the settings options aliases.
     *
     * @since 4.14
     *
     * @return string[]
     */
    protected function getSettingsAliases()
    {
        return [
            'title_is_link' => 'title_link',
            'title_max_length' => 'title_limit',
            'limit' => 'feed_limit',
            'date_enabled' => 'date_enable',
            'date_prefix' => 'text_preceding_date',
            'date_format' => 'date_format',
            'date_use_time_ago' => 'time_ago_format_enable',
            'source_enabled' => 'source_enable',
            'source_prefix' => 'text_preceding_source',
            'source_is_link' => 'source_link',
            'author_enabled' => 'authors_enable',
            'pagination_type' => 'pagination',
            'links_nofollow' => 'follow_dd',
            'links_behavior' => 'open_dd',
            'links_video_embed_page' => 'video_link',
        ];
    }

    /**
     * Retrieves the mapping of data keys to the key of the corresponding data set.
     *
     * The settings aliases are used to construct this mapping, since the only explicit mapping we need are for
     * settings keys. Any other keys will default to the meta data set.
     *
     * @since 4.14
     *
     * @return array
     */
    protected function getKeyMapping()
    {
        $keys = array_keys($this->getSettingsAliases());
        $values = array_fill(0, count($keys), 'settings');

        return array_combine($keys, $values);
    }

    /**
     * Retrieves the mask for which settings option keys to retain in the dataset.
     *
     * @since 4.14
     *
     * @return bool[]
     */
    protected function getSettingsMask()
    {
        $keys = array_keys($this->getSettingsAliases());
        $values = array_fill(0, count($keys), true);

        return array_combine($keys, $values);
    }
}
