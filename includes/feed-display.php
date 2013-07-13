<?php
    /**
     * Feed display related functions
     *
     * @package WPRSSAggregator
     */


    /**
     * Retrieve settings and prepare them for use in the display function
     *
     * @since 3.0
     */
    function wprss_get_display_settings( $settings ) {

        switch ( $settings['open_dd'] ) {

            case 'Lightbox' :
                $display_settings['open'] = 'class="colorbox"';
                break;

            case 'New window' :
                $display_settings['open'] = 'target="_blank"';
                break;
        }

        switch ( $settings['follow_dd'] ) {

            case 'No follow' :
                $display_settings['follow'] = 'rel="nofollow"';
                break;
        }

        do_action( 'wprss_get_settings' );

        return $display_settings;
    }


    /**
     * Merges the default arguments with the user set arguments
     *
     * @since 3.0
     */
    function wprss_get_shortcode_default_args( $args ) {
        // Default shortcode/function arguments for displaying feed items
        $shortcode_args = apply_filters(
                            'wprss_shortcode_args',
                            array(
                                  'links_before' => '<ul class="rss-aggregator">',
                                  'links_after'  => '</ul>',
                                  'link_before'  => '<li class="feed-item">',
                                  'link_after'   => '</li>'
                            )
        );

        // Parse incoming $args into an array and merge it with $shortcode_args
        $args = wp_parse_args( $args, $shortcode_args );

        return $args;
    }


    /**
     * Prepares and builds the query for fetching the feed items
     *
     * @since 3.0
     */
    function wprss_get_feed_items_query( $settings ) {
		$feed_items_args = array(
			'post_type'      => 'wprss_feed_item',
			'posts_per_page' => $settings['feed_limit'],
			'orderby'        => 'meta_value',
			'meta_key'       => 'wprss_item_date',
			'order'          => 'DESC'
		);

		if ( isset( $settings['source'] ) ) {
			$feeds = array_filter( array_map( 'intval', explode( ',', $settings['source'] ) ) );
            foreach ( $feeds as $feed )
                trim( $feed );
			if ( !empty( $feeds ) ) {
				$feed_items_args['meta_query'] = array(
					array(
						'key'     => 'wprss_feed_id',
						'value'   => $feeds,
						'type'    => 'numeric',
						'compare' => 'IN',
					),
				);
			}
		}

        // Arguments for the next query to fetch all feed items
        $feed_items_args = apply_filters( 'wprss_display_feed_items_query', $feed_items_args, $settings );

        // Query to get all feed items for display
        $feed_items = new WP_Query( $feed_items_args );

        return $feed_items;
    }


    add_action( 'wprss_display_template', 'wprss_default_display_template', 10, 3 );
    /**
     * Default template for feed items display
     *
     * @since 3.0
     */
    function wprss_default_display_template( $display_settings, $args, $feed_items ) {

        $general_settings = get_option( 'wprss_settings_general' );
        $excerpts_settings = get_option( 'wprss_settings_excerpts' );
        $thumbnails_settings = get_option( 'wprss_settings_thumbnails' );
        // Declare each item in $args as its own variable
        extract( $args, EXTR_SKIP );

        $output = '';

        if( $feed_items->have_posts() ) {

            $output .= "$links_before";
            $output .= get_locale();

            while ( $feed_items->have_posts() ) {
                $feed_items->the_post();
                $permalink       = get_post_meta( get_the_ID(), 'wprss_item_permalink', true );
                $feed_source_id  = get_post_meta( get_the_ID(), 'wprss_feed_id', true );
                $source_name     = get_the_title( $feed_source_id );
                do_action( 'wprss_get_post_data' );

                // convert from Unix timestamp
                $date = date_i18n( $general_settings['date_format'], intval( get_post_meta( get_the_ID(), 'wprss_item_date', true ) ) );

                if ( $general_settings['title_link'] == 1 ) {
                    $output .= "$link_before" . '<a ' . $display_settings['open'] . ' ' . $display_settings['follow'] . ' href="'. $permalink . '">'. get_the_title(). '</a>';
                }
                else {
                    $output .= get_the_title();
                }

                if ( ( $general_settings['source_enable'] == 1 ) && ( $general_settings['date_enable'] == 1 ) )  {
                    $output .= '<div class="source-date"><span class="feed-source">' .
                    ( !empty( $general_settings['text_preceding_source'] ) ? $general_settings['text_preceding_source'] . ' ' : '' ) . $source_name . ' | ' .
                    ( !empty( $general_settings['text_preceding_date'] ) ? $general_settings['text_preceding_date'] . ' ' : '' ) . $date .
                    '</span></div>' . "$link_after";
                }

                else if ( ( $general_settings['source_enable'] == 1 ) && ( $general_settings['date_enable'] == 0 ) )  {
                    $output .= '<div class="source-date"><span class="feed-source">' .
                    ( !empty( $general_settings['text_preceding_source'] ) ? $general_settings['text_preceding_source'] . ' ' : '' ) . $source_name .
                    '</span></div>' . "$link_after";
                }

                else if ( ( $general_settings['source_enable'] == 0 ) && ( $general_settings['date_enable'] == 1 ) )  {
                    $output .= '<div class="source-date"><span class="feed-source">' .
                    ( !empty( $general_settings['text_preceding_date'] ) ? $general_settings['text_preceding_date'] . ' ' : '' ) . $date .
                    '</span></div>' . "$link_after";
                }

                else {}

            }
            $output .= "$links_after";
            $output = apply_filters( 'feed_output', $output );

            echo $output;

                            // echo paginate_links();

            wp_reset_postdata();

        } else {
            $output = apply_filters( 'no_feed_items_found', __( 'No feed items found.', 'wprss' ) );
            echo $output;
        }
    }


    /**
     * Display feed items on the front end (via shortcode or function)
     *
     * @since 2.0
     */
    function wprss_display_feed_items( $args = array() ) {
        $settings = get_option( 'wprss_settings_general' );
        $display_settings = wprss_get_display_settings( $settings );
        $args = wprss_get_shortcode_default_args( $args );

        $args = apply_filters( 'wprss_shortcode_args', $args );

        $query_args = $settings;
		if ( isset( $args['limit'] ) ) {
			$query_args['feed_limit'] = filter_var( $args['limit'], FILTER_VALIDATE_INT, array(
				'options' => array(
					'min_range' => 1,
					'default'   => $query_args['feed_limit'],
				),
			) );
		}

		if ( isset( $args['source'] ) ) {
			$query_args['source'] = $args['source'];
		}

		$feed_items = wprss_get_feed_items_query( $query_args );

        do_action( 'wprss_display_template', $display_settings, $args, $feed_items );
    }


    /**
     * Redirects to wprss_display_feed_items
     * It is used for backwards compatibility to versions < 2.0
     *
     * @since 2.1
     */
    function wp_rss_aggregator( $args = array() ) {
        wprss_display_feed_items( $args );
    }


    /**
     * Limits a phrase/content to a defined number of words
     *
     * NOT BEING USED as we're using the native WP function, although the native one strips tags, so I'll
     * probably revisit this one again soon.
     *
     * @since  3.0
     * @param  string  $words
     * @param  integer $limit
     * @param  string  $append
     * @return string
     */
    function wprss_limit_words( $words, $limit, $append = '' ) {
           /* Add 1 to the specified limit becuase arrays start at 0 */
           $limit = $limit + 1;
           /* Store each individual word as an array element
              up to the limit */
           $words = explode( ' ', $words, $limit );
           /* Shorten the array by 1 because that final element will be the sum of all the words after the limit */
           array_pop( $words );
           /* Implode the array for output, and append an ellipse */
           $words = implode( ' ', $words ) . $append;
           /* Return the result */
           return rtrim( $words );
    }