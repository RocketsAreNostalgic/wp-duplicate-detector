<?php
namespace OrionRush\DuplicateDetector\Enabled;
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
if ( ! is_admin() ) {
	return;
}

// Load search term filter an sql query generator
require_once( __DIR__ . '/search-term-filter.php' );
require_once( __DIR__ . '/sql-query-generator.php' );

/**
 * Enqueues styles and scripts onto post types if they have been enabled in the admin settings page.
 *
 * @since       0.0.1
 * @author      orionrush
 */
function enqueue_dd_in_admin() {
	$settings = \OrionRush\DuplicateDetector\Admin\get_settings();
	if ( empty( $settings['post_types'] ) ) {
		return;
	}

	if ( in_array( get_post_type(), $settings['post_types'] ) ) {
		wp_enqueue_style( 'orionrush-duplicate-detector', plugins_url( '/assets/styles/duplicate-detector.css', DD_DIR ), array() );
		wp_enqueue_script( 'orionrush-duplicate-detector', plugins_url( '/assets/scripts/duplicate-detector-min.js', DD_DIR ), array( 'jquery' ), true );
		wp_localize_script( 'orionrush-duplicate-detector', 'object_DD', array(
				'button_notice' => esc_html__( 'Check for duplicate post titles.', 'dupdetect' ),
				'error_message' => esc_html__( 'Hey! We received an error:', 'dupdetect' ),
				'debug'         => WP_DEBUG,
				'plugin_url'    => plugin_dir_url( __DIR__ )
			)
		);
	}

	return $settings;
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_dd_in_admin' );

/*
 * Process the ajax request
 * In case PHP errors screw up ajax requests, enable debugging log in wp-config.php to see the error
 * http://wordpress.stackexchange.com/q/184226/13551
 *
 * @since       0.0.1
 * @author      orionrush
 *
 * @uses:       title_check
 * @global:     $wpdb
 * @wp-hook:    wp_ajax_title_check
 *
 *
 */

function ajax_callback() {
	$dupes_found_head_notice = __( 'Whoa there!', 'orionrush_duplicate_detector' );
	if ( has_filter( 'dd_dupes_response_head_text' ) ) {
		/**
		 * Filter the dupes found response, head notice.
		 *
		 * @since    0.0.3
		 * @param string $dupes_found_head_notice
		 */
		$dupes_found_head_text = apply_filters( 'dd_dupes_response_head_text', $dupes_found_head_notice );
	}

	$dupes_found_head_text = __( 'We found the following with a similar heading:', 'orionrush_duplicate_detector' );
	if ( has_filter( 'dd_dupes_response_head_text' ) ) {
		/**
		 * Filter the dupes found response, head text.
		 *
		 * @since    0.0.3
		 * @param string $dupes_found_foot_text
		 */
		$dupes_found_head_text = apply_filters( 'dd_dupes_response_head_text', $dupes_found_head_text );
	}

	$dupes_found_foot_text = sprintf( __( 'The title(s) listed above look very similar to this one. Consider making your title more unique, or perhaps move it to the trash. %s %sAlso pay attention to your permalink for good SEO.%s', 'orionrush_duplicate_detector' ), '<br/>', '<em>', '</em>' );
	if ( has_filter( 'dd_dupes_response_foot_text' ) ) {
		/**
		 * Filter the dupes found response, footer text.
		 *
		 * @since    0.0.3
		 * @param string $dupes_found_foot_text
		 */
		$dupes_found_foot_text = apply_filters( 'dd_dupes_response_foot_text', $dupes_found_foot_text );
	}

	$confirmation_text = __( 'This title looks unique!', 'orionrush_duplicate_detector' );
	if ( has_filter( 'dd_response_confirmation_text' ) ) {
		/**
		 * Filter the confirmation text.
		 *
		 * @since    0.0.3
		 * @param string $confirmation_text
		 */
		$confirmation_text = apply_filters( 'dd_response_confirmation_text', $confirmation_text );
	}

	$too_short_text = __( 'For performance reasons, this title is really too short for us to search for.', 'orionrush_duplicate_detector' );
	if ( has_filter( 'dd_response_too_sort_text' ) ) {
		/**
		 * Filter the confirmation text.
		 *
		 * @since    0.0.3
		 * @param string $too_short_text
		 */
		$too_short_text = apply_filters( 'dd_response_too_sort_text', $too_short_text );
	}

	// Grab details from inbound POST array
	$title     = $_POST['post_title'];
	$post_id   = $_POST['post_id'];
	$post_type = get_post_type( $post_id );

	$too_short = strlen($title)<= 3;

	if (!$too_short) {

		// Test to see if current post type should be isolated from the others during search.
		$options_array = get_option( 'orionrush_duplicate_detector' );
		if ( in_array( $post_type, $options_array['post_types_isolate'] ) ) {
			$post_types = $post_type;
		} else {

			$post_types_array = array_diff( $options_array['post_types'], $options_array['post_types_isolate'] );

			// Expects a space separated list...
			$post_types = (string) implode( ' ', $post_types_array );

			\OrionRush\DuplicateDetector\Helpers\write_log( 'DD callback, post-types being searched:' );
			\OrionRush\DuplicateDetector\Helpers\write_log( $post_types );
		}

		// Get any matches for post title
		$sim_results = get_any_matches( $title, $post_id, 'publish', $post_types, '0' );

		// if there are any matches
		if ( $sim_results ) {
			$notice = array(
				"head_notice" => $dupes_found_head_notice,
				"head_text"   => $dupes_found_head_text,
				"foot"        => "$dupes_found_foot_text"
			);
			foreach ( $sim_results as $sim_result ) {
				$details['ID']   = $sim_result->ID;
				$path            = 'post.php?post=' . $sim_result->ID . '&action=edit';
				$details['link'] = esc_url( admin_url( $path ) );

				// If the current user cant edit the post give a link to the public page instead
				if ( ! current_user_can( 'edit_post', $sim_result->ID ) ) {
					$details['link'] = esc_url( get_permalink( $sim_result->ID ) );
				}

				$details['type']  = get_post_type( $sim_result->ID );
				$author_id        = get_post_field( 'post_author', $sim_result->ID );
				$author_name      = get_the_author_meta( 'display_name', $author_id );
				$details['title'] = get_the_title( $sim_result->ID ) . " (" . $details['type'] . " by: " . $author_name . ")";
				$posts[]          = $details;
			}
			// Found duplicates
			$return_json = array( "status" => "true", "notice" => $notice, "posts" => $posts );
		} else {
			// No duplicates - yay!
			$return_json = array( "status" => "false", "notice" => $confirmation_text );
		}
	} else {
		// Too short
		$return_json = array( "status" => "too-short", "notice" => $too_short_text );
	}

	if ( has_filter( 'dd_response_return_json' ) ) {
		/**
		 * Filter the json response.
		 *
		 * @since    0.0.4
		 * @param string $return_json
		 */
		$return_json = apply_filters( 'dd_response_return_json', $return_json );
	}
	// Log these values if we're debugging
	\OrionRush\DuplicateDetector\Helpers\write_log( "DD Returned JSON as array:" );
	\OrionRush\DuplicateDetector\Helpers\write_log( $return_json );

	if ( ob_get_length() )
		// Flush buffers to default point
		// http://wordpress.stackexchange.com/q/184226/13551
		// http://php.net/manual/en/function.exit.php#101204
	{
		ob_clean();
	}
	header( 'Content-Type: application/json' );
	exit( json_encode( $return_json ) );
}