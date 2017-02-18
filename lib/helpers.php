<?php
namespace OrionRush\DuplicateDetector\Helpers;
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * A simple logging function good for troubleshooting ajax etc.
 *
 * @param $log // the message or array to be printed to the log
 * @param bool $force // Force a log even if WP_DEBUG_LOG is not enabled
 *
 */

function write_log( $log, $force = false ) {
	if ( true === WP_DEBUG_LOG || $force ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}