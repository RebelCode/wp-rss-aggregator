<?php
    /**
     * Feed display related functions
     *
     * @package WPRSSAggregator
     */

    /**
     * Display template for a feed source. Simulates a shortcode call.
     *
     * @since 4.6.6
     * @deprecated 4.13 This function was left here because the ET addon references it.
     */
    function wprss_render_feed_view( $content ) {
        return $content;
    }

    /**
     * Display template for a feed source. Simulates a shortcode call.
     *
     * @since 4.6.6
     * @deprecated 4.13 This function was left here because the ET addon references it.
     */
    function wprss_render_feed_item_view( $content ) {
        return $content;
    }

    /**
     * Redirects to wprss_display_feed_items
     * It is used for backwards compatibility to versions < 2.0
     *
     * @since 2.1
     */
    function wp_rss_aggregator( $args = array() ) {
        $template = wpra_get('feeds/templates/master_template');
        $fullArgs = $args;

        // Use legacy mode if arg was not explicitly given
        if (!isset($fullArgs['legacy'])) {
            $fullArgs['legacy'] = true;
        }

        return $template->render($args);
    }

    /**
     * Handles the display for a single feed item.
     *
     * @since 4.6.6
     */
    function wprss_display_single_feed_item( $atts = array() ) {
        if ( empty( $atts ) ) return;
        $id = empty( $atts['id'] ) ? FALSE : $atts['id'];
        if ( $id === FALSE || get_post_type( $id ) !== 'wprss_feed_item' || ( $item = get_post( $id ) ) === FALSE ) {
            return '';
        }
        //Enqueue scripts / styles
        wp_enqueue_script( 'jquery.colorbox-min', WPRSS_JS . 'jquery.colorbox-min.js', array( 'jquery' ) );
        wp_enqueue_script( 'wprss_custom', WPRSS_JS . 'custom.js', array( 'jquery', 'jquery.colorbox-min' ) );

        setup_postdata( $item );
        $output = wprss_render_feed_item( $id );
        $output = apply_filters( 'wprss_shortcode_single_output', $output );
        wp_reset_postdata();
        return $output;
    }

    /**
     * Renders a single feed item.
     *
     * @param int    $ID      The ID of the feed item to render
     * @param string $default The default text to return if something fails.
     * @return string The output
     * @since 4.6.6
     */
    function wprss_render_feed_item( $ID = NULL, $default = '', $args = array() ) {
        $ID = ( $ID === NULL )? get_the_ID() : $ID;
        if ( is_feed() ) return $default;

        // Prepare the options
        $general_settings = get_option( 'wprss_settings_general' );
        $display_settings = wprss_get_display_settings( $general_settings );
        $excerpts_settings = get_option( 'wprss_settings_excerpts' );
        $thumbnails_settings = get_option( 'wprss_settings_thumbnails' );

		$args = wp_parse_args($args, array(
			'link_before'			=> '',
			'link_after'			=> ''
		));
        $extra_options = apply_filters( 'wprss_template_extra_options', array(), $args);

        // Declare each item in $args as its own variable
        extract( $args, EXTR_SKIP );

        // Get the item meta
        $permalink       = get_post_meta( $ID, 'wprss_item_permalink', true );
        $enclosure       = get_post_meta( $ID, 'wprss_item_enclosure', true );
        $feed_source_id  = get_post_meta( $ID, 'wprss_feed_id', true );
        $link_enclosure  = get_post_meta( $feed_source_id, 'wprss_enclosure', true );
        $source_name     = get_the_title( $feed_source_id );
        $source_url      = get_post_meta( $feed_source_id, 'wprss_site_url', true );
        $timestamp       = get_the_time( 'U', $ID );

        $general_source_link = isset( $general_settings['source_link'] ) ? $general_settings['source_link'] : 0;
        $feed_source_link = get_post_meta( $feed_source_id, 'wprss_source_link', true );
        $source_link = ( $feed_source_link === '' || intval($feed_source_link) < 0 ) // If not explicit value
            ? $general_source_link // Fall back to general setting
            : $feed_source_link; // Otherwise, use value
        $source_link = intval(trim($source_link));

        // Fallback for feeds created with older versions of the plugin
        if ( $source_url === '' ) $source_url = get_post_meta( $feed_source_id, 'wprss_url', true );
        // convert from Unix timestamp
        $date = wprss_date_i18n( $timestamp );

        // Prepare the title
        $feed_item_title = get_the_title();
        $feed_item_title_link = ( $link_enclosure === 'true' && $enclosure !== '' )? $enclosure : $permalink;

        // Prepare the text that precedes the source
        $text_preceding_source = wprss_get_general_setting('text_preceding_source');
        $text_preceding_source = ltrim( __( $text_preceding_source, WPRSS_TEXT_DOMAIN ) . ' ' );

        $text_preceding_date = wprss_get_general_setting('text_preceding_date');
        $text_preceding_date = ltrim( __( $text_preceding_date, WPRSS_TEXT_DOMAIN ) . ' ' );

        do_action( 'wprss_get_post_data' );

        $meta = $extra_options;
        $extra_meta = apply_filters( 'wprss_template_extra_meta', $meta, $args, $ID );

        ///////////////////////////////////////////////////////////////
        // BEGIN TEMPLATE

        // Prepare the output
        $output = '';
        // Begin output buffering
        ob_start();
        // Print the links before
        echo $link_before;

        // The Title
        $item_title = wprss_link_display( $feed_item_title_link, $feed_item_title, wprss_get_general_setting('title_link') );
        $item_title = apply_filters('wprss_item_title', $item_title, $feed_item_title_link, $feed_item_title, wprss_get_general_setting('title_link'));
        echo $item_title;

        do_action( 'wprss_after_feed_item_title', $extra_meta, $display_settings, $ID );

        // FEED ITEM META
        echo '<div class="wprss-feed-meta">';

        // SOURCE
        if ( wprss_get_general_setting('source_enable') == 1 ) {
            echo '<span class="feed-source">';
            $source_link_text = apply_filters('wprss_item_source_link', wprss_link_display( $source_url, $source_name, $source_link ) );
            $source_link_text = $text_preceding_source . $source_link_text;
            echo $source_link_text;
            echo '</span>';
        }

        // DATE
        if ( wprss_get_general_setting('date_enable') == 1 ) {
            echo '<span class="feed-date">';
            $date_text = apply_filters( 'wprss_item_date', $date );
            $date_text = $text_preceding_date . $date_text;
            echo $date_text;
            echo '</span>';
        }

        // AUTHOR
        $author = get_post_meta( $ID, 'wprss_item_author', TRUE );
        if ( wprss_get_general_setting('authors_enable') == 1 && $author !== NULL && is_string( $author ) && $author !== '' ) {
            echo '<span class="feed-author">';
            $author_text = apply_filters( 'wprss_item_author', $author );
            $author_prefix_text = apply_filters( 'wprss_author_prefix_text', 'By' );
            _e( $author_prefix_text, WPRSS_TEXT_DOMAIN );
            echo ' ' . $author_text;
            echo '</span>';
        }

        echo '</div>';

        // TIME AGO
        if ( wprss_get_general_setting('date_enable') == 1 && wprss_get_general_setting('time_ago_format_enable') == 1 ) {
            $time_ago = human_time_diff( $timestamp, time() );
            echo '<div class="wprss-time-ago">';
            $time_ago_text = apply_filters( 'wprss_item_time_ago', $time_ago );
            printf( __( '%1$s ago', WPRSS_TEXT_DOMAIN ), $time_ago_text );
            echo '</div>';
        }

        // END TEMPLATE - Retrieve buffered output
        $output .= ob_get_clean();
        $output = apply_filters( 'wprss_single_feed_output', $output, $permalink );
        $output .= "$link_after";

        // Print the output
        return $output;
    }


    /**
     * Retrieve settings and prepare them for use in the display function
     *
     * @since 3.0
     */
    function wprss_get_display_settings( $settings = NULL ) {
        if ( $settings === NULL ) {
            $settings = get_option( 'wprss_settings_general' );
        }
        // Parse the arguments together with their default values
        $args = wp_parse_args(
            $settings,
            array(
                'open_dd'   =>  'blank',
                'follow_dd' =>  '',
            )
        );

        // Prepare the 'open' setting - how to open links for feed items
        $open = '';
        switch ( $args['open_dd'] ) {
            case 'lightbox' :
                $open = 'class="colorbox"';
                break;
            case 'blank' :
                $open = 'target="_blank"';
                break;
        }

        // Prepare the 'follow' setting - whether links marked as nofollow or not
        $follow = ( $args['follow_dd'] == 'no_follow' )? 'rel="nofollow"' : '';

        // Prepare the final settings array
        $display_settings = array(
            'open'      =>  $open,
            'follow'    =>  $follow
        );

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
		if( isset( $settings['feed_limit'] ) ) {
			$posts_per_page = $settings['feed_limit'];
		} else {
			$posts_per_page = wprss_get_general_setting('feed_limit');
		}
        global $paged;
        if ( get_query_var('paged') ) {
            $paged = get_query_var('paged');
        } elseif ( get_query_var('page') ) {
            $paged = get_query_var('page');
        } else {
            $paged = 1;
        }

		$feed_items_args = array(
		    'post_type'           => get_post_types(),
            'posts_per_page'      => $posts_per_page,
			'orderby'             => 'date',
			'order'               => 'DESC',
            'paged'               => $paged,
            'suppress_filters'    => true,
            'ignore_sticky_posts' => true,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'wprss_feed_id',
                    'compare' => 'EXISTS',
                )
            )
		);

        if ( isset($settings['pagination']) ) {
            $pagination = strtolower( $settings['pagination'] );
            if ( in_array( $pagination, array('false','off','0') ) ) {
                unset( $feed_items_args['paged'] );
            }
        }

        if ( isset( $settings['no-paged'] ) && $settings['no-paged'] === TRUE ) {
            unset( $feed_items_args['no-paged'] );
        }

		// If either the source or exclude arguments are set (but not both), prepare a meta query
		if ( isset( $settings['source'] ) xor isset( $settings['exclude'] ) ) {
			// Set the appropriate setting and operator
			$setting = 'source';
			$operator = 'IN';
			if ( isset( $settings['exclude'] ) ) {
				$setting = 'exclude';
				$operator = 'NOT IN';
			}
			$feeds = array_filter( array_map( 'intval', explode( ',', $settings[$setting] ) ) );
            foreach ( $feeds as $feed )
                trim( $feed );
			if ( !empty( $feeds ) ) {
				$feed_items_args['meta_query'] = array(
					array(
						'key'     => 'wprss_feed_id',
						'value'   => $feeds,
						'type'    => 'numeric',
						'compare' => $operator,
					),
				);
			}
		}

        // Arguments for the next query to fetch all feed items
        $feed_items_args = apply_filters( 'wprss_display_feed_items_query', $feed_items_args, $settings );

        // Query to get all feed items for display
        $feed_items = new WP_Query( $feed_items_args );

        if ( isset( $settings['get-args'] ) && $settings['get-args'] === TRUE ) {
            return $feed_items_args;
        } else return $feed_items;
    }


    add_action( 'wprss_display_template', 'wprss_default_display_template', 10, 3 );
    /**
     * Default template for feed items display
     *
     * @since 3.0
     * @param $args       array    The shortcode arguments
     * @param $feed_items WP_Query The feed items to display
     */
    function wprss_default_display_template( $args, $feed_items ) {
        global $wp_query;
        global $paged;

        // Swap the current WordPress Query with our own
        $old_wp_query = $wp_query;
        $wp_query = $feed_items;

        // Prepare the output
        $output = '';

        // Check if our current query returned any feed items
        if ( $feed_items->have_posts() ) {
            // PRINT LINKS BEFORE LIST OF FEED ITEMS
            $output .= $args['links_before'];

            // FOR EACH ITEM
            while ( $feed_items->have_posts() ) {
                // Get the item
                $feed_items->the_post();
                // Add the output
                $output .= wprss_render_feed_item( NULL, '', $args );
            }

            // OUTPUT LINKS AFTER LIST OF FEED ITEMS
            $output .= $args['links_after'];

            // Add pagination if needed
            if ( !isset( $args['pagination'] ) || !in_array( $args['pagination'], array('off','false','0',0) ) ) {
                $output = apply_filters( 'wprss_pagination', $output );
            }

            // Filter the final output, and print it
            echo apply_filters( 'feed_output', $output );
        } else {
            // No items found message
            echo apply_filters( 'no_feed_items_found', __( 'No feed items found.', WPRSS_TEXT_DOMAIN ) );
        }

        // Reset the WordPress query
        $wp_query = $old_wp_query;
        wp_reset_postdata();
    }


    /**
     * Generates an HTML link, using the saved display settings.
     *
     * @param string $link The link URL
     * @param string $text The link text to display
     * @param string $bool Optional boolean. If FALSE, the text is returned unlinked. Default: TRUE.
     * @return string The generated link
     * @since 4.2.4
     */
    function wprss_link_display( $link, $text, $bool = TRUE ) {
        $display_settings = wprss_get_display_settings( get_option( 'wprss_settings_general' ) );
        $a = $bool ? "<a {$display_settings['open']} {$display_settings['follow']} href='$link'>$text</a>" : $text;
        return $a;
    }


    add_filter( 'wprss_pagination', 'wprss_pagination_links' );
    /**
     * Display pagination links
     *
     * @since 3.5
     */
    function wprss_pagination_links( $output ) {
		// Get the general setting
		$pagination = wprss_get_general_setting( 'pagination' );;

		// Check the pagination setting, if using page numbers
		if ( $pagination === 'numbered' ) {
			global $wp_query;
			$big = 999999999; // need an unlikely integer
			$output .= paginate_links( array(
				'base'		=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'	=> '?paged=%#%',
				'current'	=> max( 1, get_query_var('paged') ),
				'total'		=> $wp_query->max_num_pages
			) );
			return $output;
		}
		// Otherwise, using default paginations
		else {
			$output .= '<div class="nav-links">';
			$output .= '    <div class="nav-previous alignleft">' . get_next_posts_link( __( 'Older posts', WPRSS_TEXT_DOMAIN ) ) . '</div>';
			$output .= '    <div class="nav-next alignright">' . get_previous_posts_link( __( 'Newer posts', WPRSS_TEXT_DOMAIN ) ) . '</div>';
			$output .= '</div>';
			return $output;
		}
    }


    add_filter( 'the_title', 'wprss_shorten_title', 10, 2 );
    /**
     * Checks the title limit option and shortens the title when necassary.
     *
     * @since 1.0
     */
    function wprss_shorten_title( $title, $id = null ) {
        if ( $id === null ) return $title;
        // Get the option. If does not exist, use 0, which is ignored.
        $general_settings = get_option( 'wprss_settings_general' );
        $title_limit = isset( $general_settings['title_limit'] )? intval( $general_settings['title_limit'] ) : 0;
        // Check if the title is for a wprss_feed_item, and check if trimming is needed
        if ( isset( $id ) && get_post_type( $id ) === 'wprss_feed_item' && $title_limit > 0 && strlen( $title ) > $title_limit ) {
            // Return the trimmed version of the title
            return substr( $title, 0, $title_limit ) . apply_filters( 'wprss_shortened_title_ending', '...' );
        }
        // Otherwise, return the same title
        return $title;
    }


    /**
     * Display feed items on the front end (via shortcode or function)
     *
     * @since 2.0
     */
    function wprss_display_feed_items( $args = array() ) {
        $settings = get_option( 'wprss_settings_general' );
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

        if ( isset( $args['pagination'] ) ) {
            $query_args['pagination'] = $args['pagination'];
        }

		if ( isset( $args['source'] ) ) {
			$query_args['source'] = $args['source'];
		}
		elseif ( isset( $args['exclude'] ) ) {
			$query_args['exclude'] = $args['exclude'];
		}

        $query_args = apply_filters( 'wprss_process_shortcode_args', $query_args, $args );

		$feed_items = wprss_get_feed_items_query( $query_args );

        do_action( 'wprss_display_template', $args, $feed_items );
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
