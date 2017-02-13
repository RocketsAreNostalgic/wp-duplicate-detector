<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	global $wpdb;
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );

	if ( ! empty( $blogs ) ) {
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			delete_option( 'orionrush_duplicate_detector' );
		}
	}
} else {
	delete_option( 'orionrush_duplicate_detector' );
}