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
 * @TODO: Add filters for alert text, and footer
 * @TODO: Add mechanism for adding custom fields values in alerts
 *
 */

function ajax_callback() {
	$dupes_found_head_text = __( 'Whoa there! We found the following entries with a similar heading:', 'dupdetect' );
	if ( has_filter( 'dd_dupes_response_head_text' ) ) {
		$dupes_found_head_text = apply_filters( 'dd_dupes_response_head_text', $dupes_found_head_text );
	}

	$dupes_found_foot_text = sprintf( __( 'The title(s) listed above look very similar to this one. Consider making your title more specific, or perhaps move it to the trash. %s Also pay attention to your permalink for good SEO.', 'dupdetect' ), '<br/>' );
	if ( has_filter( 'dd_dupes_response_foot_text' ) ) {
		$dupes_found_foot_text = apply_filters( 'dd_dupes_response_foot_text', $dupes_found_foot_text );
	}

	$confirmation_text = __( 'This title looks unique!', 'dupdetect' );
	if ( has_filter( 'dd_response_confirmation_text' ) ) {
		$confirmation_text = apply_filters( 'dd_response_confirmation_text', $confirmation_text );
	}

	// Grab details from inbound POST array
	$title      = $_POST['post_title'];
	$post_id    = $_POST['post_id'];
	$post_types = get_post_type( $post_id );

	// Get any matches for post title
	$sim_results = get_any_matches( $title, $post_id, 'publish', $post_types, '0' );

	// if there are any matches
	if ( $sim_results ) {
		$notice = array( "head" => $dupes_found_head_text, "foot" => "$dupes_found_foot_text" );
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
		$return_json = array( "status" => "true", "notice" => $notice, "posts" => $posts );
	} else {
		$return_json = array( "status" => "false", "notice" => $confirmation_text );
	}

	// Log these values if we're debugging
	\OrionRush\DuplicateDetector\write_log( "DD Returned JSON as array:" );
	\OrionRush\DuplicateDetector\write_log( $return_json );

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