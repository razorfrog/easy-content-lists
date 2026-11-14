<?php

/**
 * Plugin Name:       Easy Content Lists
 * Plugin URI:        https://razorfrog.com/easy-content-lists/
 * Description:       Shortcodes for easily listing all your pages, posts, taxonomies, and tags.
 * Version:           1.0.5
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Razorfrog Web Design
 * Author URI:        https://razorfrog.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       easy-content-lists
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues plugin styles.
 *
 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_style/ Reference for wp_enqueue_style().
 * @link https://developer.wordpress.org/reference/functions/plugin_dir_url/ Reference for plugin_dir_url().
 */
function rflists_enqueue_styles() {
		$css_url     = plugin_dir_url( __FILE__ ) . 'style.css';
		$css_path    = plugin_dir_path( __FILE__ ) . 'style.css';
		$css_version = file_exists( $css_path ) ? (string) filemtime( $css_path ) : '1.0.5';
		wp_enqueue_style( 'razorfrog-plugin-styles', $css_url, array(), $css_version );
}
add_action( 'wp_enqueue_scripts', 'rflists_enqueue_styles' );


/******************************************************************************************************************/
/* Output when a list has nothing to show. */
/******************************************************************************************************************/

if ( ! function_exists( 'rflists_empty_output' ) ) {
		/**
 		* What a list shows when it finds nothing.
 		*
 		* The built-in message (e.g. "ERROR: Can't find posts.") is only shown to people who can edit
 		* the site, so visitors never see an error on a list that is simply empty right now (no rental
 		* listings, no posts in a category yet). A custom error_message is shown to everyone, and
 		* error_message="" shows nothing at all.
 		*
 		* @param string $message The error_message attribute as used.
 		* @param string $default The shortcode's built-in message.
 		* @return string HTML, or an empty string.
 		*/
		function rflists_empty_output( $message, $default ) {
				if ( '' === trim( $message ) ) {
						return '';
				}
				if ( $message === $default && ! current_user_can( 'edit_posts' ) ) {
						return '';
				}
				return "<p class='rf-list-error'>" . esc_html( $message ) . '</p>';
		}
}

/******************************************************************************************************************/
/* Pages list shortcode [razorfrog_pages_list]. */
/******************************************************************************************************************/

if ( ! shortcode_exists( 'razorfrog_pages_list' ) ) {
		/**
 		* Sorts pages into a hierarchical structure.
 		*
 		* @param array &$pages Array of pages to be sorted.
 		* @param array &$hierarchy The sorted hierarchical array of pages.
 		* @param int   $parent_id The ID of the parent page. Default is 0.
 		*/
		function rflists_sort_pages_hierarchically( &$pages, &$hierarchy, $parent_id = 0 ) {
				foreach ( $pages as $i => $page ) {
						if ( $page->post_parent === $parent_id ) {
								$hierarchy[] = $page;
								unset( $pages[ $i ] );
						}
				}

				foreach ( $hierarchy as $top_page ) {
						$top_page->children = array();
						rflists_sort_pages_hierarchically( $pages, $top_page->children, $top_page->ID );
				}
		}

		function rflists_filter_get_pages_args( $query_args, $parsed_args ) {
			$query_args['has_password'] = false; // exclude password-protected pages
			return $query_args;
		}

		/**
 		* Recursively generates HTML markup to display pages in a hierarchical structure.
 		*
 		* @param array $pages Array of pages to display.
 		* @param int   $col Column number for CSS class. Used for styling purposes.
 		* @param int   $loop Loop iteration counter to determine the level of nesting.
 		* @return string HTML markup of the hierarchical page structure.
 		*/
		function rflists_print_pages_hierarchically( $pages, $col, $loop ) {
				$class = 1 === $loop ? "rf-page-list-parent rf-page-col-$col" : 'rf-page-list-child';
				$html  = "<ul class='rf-page-list $class list-pages'>";

				foreach ( $pages as $page ) {
						$title = esc_html( $page->post_title );
						$link  = esc_url( get_page_link( $page ) );
						$html .= "<li class='page-item page-item-{$page->ID}'><a href='{$link}' rel='bookmark'>{$title}</a>";

						// Child pages go inside the parent's <li>, so the nesting is valid HTML.
						if ( is_array( $page->children ) && ! empty( $page->children ) ) {
								$html .= rflists_print_pages_hierarchically( $page->children, 0, $loop + 1 );
						}

						$html .= '</li>';
				}

				$html .= '</ul>';
				return $html;
		}

		/**
 		* Shortcode function to display a list of pages in a hierarchical structure.
 		*
 		* @param array $atts Shortcode attributes.
 		* @return string HTML output of the pages list.
 		*/
		function rflists_pages_list_function( $atts ) {
				// Get shortcode parameters with defaults.
				$atts = shortcode_atts(
						array(
								'orderby'       => 'title',
								'order'         => 'ASC',
								'exclude'       => '',
								'child_of'      => 0,
								'col'           => 1,
								'error_message' => 'ERROR: Can\'t find pages.',
						),
						$atts,
						'razorfrog_pages_list'
				);

				// Prepare arguments for get_pages.
				$args = array(
						'post_status' => 'publish',
						'orderby'     => sanitize_text_field( $atts['orderby'] ),
						'order'       => sanitize_text_field( $atts['order'] ),
						'exclude'     => array_filter( array_map( 'intval', explode( ',', $atts['exclude'] ) ) ),
						'child_of'    => intval( $atts['child_of'] ),
				);

				// Retrieve pages based on arguments.
				add_filter( 'get_pages_query_args', 'rflists_filter_get_pages_args', 10, 2 );
				$pages = get_pages( $args );
				remove_filter( 'get_pages_query_args', 'rflists_filter_get_pages_args', 10 );

				// Check for pages and generate output.
				if ( $pages && ! is_wp_error( $pages ) ) {
						$pages_hierarchical = array();
						rflists_sort_pages_hierarchically( $pages, $pages_hierarchical, intval( $atts['child_of'] ) );
						$return_string = rflists_print_pages_hierarchically( $pages_hierarchical, intval( $atts['col'] ), 1 );
				} else {
						$return_string = rflists_empty_output( $atts['error_message'], 'ERROR: Can\'t find pages.' );
				}

				return $return_string;
		}
		add_shortcode( 'razorfrog_pages_list', 'rflists_pages_list_function' );
}


/******************************************************************************************************************/
/* Posts list shortcode [razorfrog_posts_list]. */
/******************************************************************************************************************/

if ( ! shortcode_exists( 'razorfrog_posts_list' ) ) {
		/**
 		* Shortcode function to display a list of posts with various filtering options.
 		*
 		* @param array $atts Shortcode attributes.
 		* @return string HTML output of the posts list.
 		*/
		function rflists_posts_list_function( $atts ) {
				// Get shortcode parameters with defaults.
				$atts = shortcode_atts(
						array(
								'post_type'      => 'post',
								'orderby'        => 'date',
								'order'          => 'DESC',
								'exclude'        => '',
								'posts_per_page' => 100,
								'col'            => 1,
								'taxonomy'       => '',
								'terms'          => '',
								'meta_key'       => '',
								'meta_value'     => '',
								'error_message'  => 'ERROR: Can\'t find posts.',
						),
						$atts,
						'razorfrog_posts_list'
				);

				// Prepare query arguments.
				$args = array(
						'post_status'    => 'publish',
						'post_type'      => sanitize_text_field( $atts['post_type'] ),
						'orderby'        => sanitize_text_field( $atts['orderby'] ),
						'order'          => sanitize_text_field( $atts['order'] ),
						'post__not_in'   => array_filter( array_map( 'intval', explode( ',', $atts['exclude'] ) ) ),
						'posts_per_page' => intval( $atts['posts_per_page'] ),
						'meta_key'       => sanitize_text_field( $atts['meta_key'] ),
						'has_password'  => false,
				);

				// Taxonomy query.
				if ( ! empty( $atts['taxonomy'] ) && ! empty( $atts['terms'] ) ) {
						$args['tax_query'] = array(
								array(
										'taxonomy' => sanitize_text_field( $atts['taxonomy'] ),
										'field'    => 'slug',
										'terms'    => explode( ',', sanitize_text_field( $atts['terms'] ) ),
										'operator' => 'IN',
								),
						);
				}

				// Meta query.
				if ( ! empty( $atts['meta_key'] ) && ! empty( $atts['meta_value'] ) ) {
						$args['meta_query'] = array(
								array(
										'key'     => sanitize_text_field( $atts['meta_key'] ),
										'value'   => sanitize_text_field( $atts['meta_value'] ),
										'compare' => '=',
								),
						);
				}

				// Custom query for posts.
				$query  = new WP_Query( $args );
				$col    = intval( $atts['col'] );
				$active = get_the_ID();

				// Generates output.
				if ( $query->have_posts() ) {
						$return_string = "<ul class='rf-post-list rf-post-list-type-" . esc_attr( sanitize_html_class( $atts['post_type'] ) ) . " rf-post-col-$col'>";
						while ( $query->have_posts() ) :
								$query->the_post();

								$class = ( get_the_ID() === $active ) ? ' active' : '';
								$title = esc_html( get_the_title() );
								$link  = esc_url( get_permalink() );

								$return_string .= "<li class='post-item post-item-" . get_the_ID() . "$class'><a href='$link' rel='bookmark'>$title</a></li>";

						endwhile;
						$return_string .= '</ul>';
				} else {
						$return_string = rflists_empty_output( $atts['error_message'], 'ERROR: Can\'t find posts.' );
				}

				// Reset postdata.
				wp_reset_postdata();
				return $return_string;
		}
		add_shortcode( 'razorfrog_posts_list', 'rflists_posts_list_function' );
}


/******************************************************************************************************************/
/* Taxonomy list shortcode [razorfrog_taxonomy_list]. */
/******************************************************************************************************************/

if ( ! shortcode_exists( 'razorfrog_taxonomy_list' ) ) {
		/**
 		* Sorts terms into a hierarchical structure.
 		*
 		* @param array &$terms Array of terms to be sorted.
 		* @param array &$hierarchy The sorted hierarchical array of terms.
 		* @param int   $parent_id The ID of the parent term. Default is 0.
 		*/
		function rflists_sort_terms_hierarchically( &$terms, &$hierarchy, $parent_id = 0 ) {
				foreach ( $terms as $i => $term ) {
						if ( $term->parent === $parent_id ) {
								$hierarchy[] = $term;
								unset( $terms[ $i ] );
						}
				}

				foreach ( $hierarchy as $top_term ) {
						$top_term->children = array();
						rflists_sort_terms_hierarchically( $terms, $top_term->children, $top_term->term_id );
				}
		}

		/**
 		* Recursively generates HTML markup to display taxonomy terms in a hierarchical structure.
 		*
 		* @param array $terms Array of terms to display.
 		* @param int   $col Column number for CSS class.
 		* @param bool  $count Whether to show term count.
 		* @param int   $loop Loop iteration counter to determine the level of nesting.
 		* @return string HTML markup of the hierarchical term structure.
 		*/
		function rflists_print_terms_hierarchically( $terms, $col, $count, $loop ) {
				$class  = 1 === $loop ? "rf-tax-list-parent rf-tax-col-$col" : 'rf-tax-child';
				$class .= $count ? ' show-count' : '';
				$html   = "<ul class='rf-tax-list $class'>";

				foreach ( $terms as $term ) {
						$name       = esc_html( $term->name );
						$link       = esc_url( get_term_link( $term ) );
						$count_span = $count ? "<span class='term-count'>" . esc_html( $term->count ) . '</span>' : '';

						$html .= "<li class='term-item term-item-{$term->term_id}'>";
						$html .= "<span class='term-item-inner'>";
						$html .= "<a href='$link'>$name</a>$count_span";
						$html .= '</span>';

						// Child terms go inside the parent's <li>, so the nesting is valid HTML.
						if ( is_array( $term->children ) && ! empty( $term->children ) ) {
								$html .= rflists_print_terms_hierarchically( $term->children, 0, $count, $loop + 1 );
						}

						$html .= '</li>';
				}

				$html .= '</ul>';
				return $html;
		}

		/**
 		* Shortcode function to display a list of taxonomy terms.
 		*
 		* @param array $atts Shortcode attributes.
 		* @return string HTML output of the taxonomy list.
 		*/
		function rflists_taxonomy_list_function( $atts ) {
				$atts = shortcode_atts(
						array(
								'taxonomy'      => 'category',
								'orderby'       => 'title',
								'order'         => 'ASC',
								'col'           => 1,
								'count'         => 'true',
								'error_message' => 'ERROR: Can\'t find taxonomy.',
						),
						$atts,
						'razorfrog_taxonomy_list'
				);

				$col   = intval( $atts['col'] );
				$count = 'true' === $atts['count'] || '1' === $atts['count'];

				$terms = get_terms(
						array(
								'taxonomy'   => sanitize_text_field( $atts['taxonomy'] ),
								'orderby'    => sanitize_text_field( $atts['orderby'] ),
								'order'      => sanitize_text_field( $atts['order'] ),
								'hide_empty' => true,
						)
				);

				if ( $terms && ! is_wp_error( $terms ) ) {
						$terms_hierarchical = array();
						rflists_sort_terms_hierarchically( $terms, $terms_hierarchical );
						$return_string = rflists_print_terms_hierarchically( $terms_hierarchical, $col, $count, 1 );
				} else {
						$return_string = rflists_empty_output( $atts['error_message'], 'ERROR: Can\'t find taxonomy.' );
				}

				return $return_string;
		}
		add_shortcode( 'razorfrog_taxonomy_list', 'rflists_taxonomy_list_function' );
}


/******************************************************************************************************************/
/* Tags list shortcode [razorfrog_tags_list]. */
/******************************************************************************************************************/

if ( ! shortcode_exists( 'razorfrog_tags_list' ) ) {
		/**
 		* Shortcode function to display a list of tags, styled as a tag cloud.
 		*
 		* @param array $atts Shortcode attributes.
 		* @return string HTML output of the tag list.
 		*/
		function rflists_tags_list_function( $atts ) {
				$atts = shortcode_atts(
						array(
								'query_type'    => 'exclude',
								'tags_ids'      => '',
								'max_count'     => 100,
								'size_from'     => 16,
								'size_to'       => 36,
								'error_message' => 'ERROR: Can\'t find tags.',
						),
						$atts,
						'razorfrog_tags_list'
				);

				$tag_args = array(
						'taxonomy'   => 'post_tag',
						'orderby'    => 'count',
						'order'      => 'DESC',
						'hide_empty' => true,
				);

				if ( 'exclude' === $atts['query_type'] ) {
						$tag_args['exclude'] = array_map( 'intval', explode( ',', $atts['tags_ids'] ) );
				} elseif ( 'include' === $atts['query_type'] ) {
						$tag_args['include'] = array_map( 'intval', explode( ',', $atts['tags_ids'] ) );
				} else {
						return rflists_empty_output( $atts['error_message'], 'ERROR: Can\'t find tags.' );
				}

				$tags = get_tags( $tag_args );

				if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
						$cloud = rflists_build_tag_cloud( $tags, $atts );
						return '<div class="rf-tag-list">' . implode( '', $cloud ) . '</div>';
				} else {
						return rflists_empty_output( $atts['error_message'], 'ERROR: Can\'t find tags.' );
				}
		}
		add_shortcode( 'razorfrog_tags_list', 'rflists_tags_list_function' );

		/**
 		* Builds a tag cloud with specified font size settings.
 		*
 		* @param array $tags Array of tag objects.
 		* @param array $atts Attributes from the shortcode.
 		* @return array Array of HTML elements for each tag.
 		*/
		function rflists_build_tag_cloud( $tags, $atts ) {
				// Font size calculation.
				$max_count    = intval( $atts['max_count'] );
				$count_spread = $tags[0]->count - end( $tags )->count;
				$font_spread  = intval( $atts['size_to'] ) - intval( $atts['size_from'] );
				$step         = $count_spread ? ( $font_spread / $count_spread ) : 0;

				$cloud = array();
				foreach ( $tags as $index => $tag ) {
						if ( $index >= $max_count ) {
								break;
						}

						$size = intval( $atts['size_from'] ) + ( ( $tag->count - end( $tags )->count ) * $step );
						$link = esc_url( get_tag_link( $tag->term_id ) );
						$name = esc_html( $tag->name );

						$cloud[] = "<p class='tag-item tag-item-{$tag->term_id}' style='font-size:{$size}px;'><a href='{$link}'>{$name}</a></p>";
				}

				sort( $cloud ); // Sort alphabetically.
				return $cloud;
		}
}
