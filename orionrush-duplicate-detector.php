<?php
namespace OrionRush\DuplicateDetector;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
Plugin Name:   Duplicate Detector
Plugin URI:    https://orionrush.com
Description:   Add lightweight plugin to help prevent post title duplication.
Version:       0.0.3
Author:        orion rush
Text Domain:   dupdetect
Author URI:    https://orionrush.com/
License:       MIT License
License URI:   http://opensource.org/licenses/MIT
*/

/**
 * PRO TIP:
 * When PHP errors screw up ajax requests, you'll see a warning to the effect of:
 * "Hey! We received an error: SyntaxError: Unexpected token <."
 * In this case enable WP_DEBUG, and WP_DEBUG_LOG in wp-config.php to record these errors, and show screen and browser console logs to help troubleshoot.
 * https://codex.wordpress.org/Debugging_in_WordPress
 */
define('DD_PLUGIN', __FILE__ ); // The location of this plugin
define('DD_PATH', plugin_dir_path(__FILE__));
define('DD_DIR', __FILE__);
define('DD_PLUGIN_NAME', "Duplicate Detector");

require_once(__DIR__ . '/lib/activation.php');
require_once(__DIR__ . '/lib/admin.php');
require_once(__DIR__ . '/lib/detector.php');

/**
 * Load the activation script
 *
 * @since       0.0.2
 * @author      orionrush
 *
 */
register_activation_hook(__FILE__, __NAMESPACE__ . '\\Activation\\activate');

/**
 * Load Text Domain for translation
 *
 * since       0.0.2
 * @author      orionrush
 *e
 */
function load_textdomain() {
    load_plugin_textdomain('duplicate_detector', false, dirname(plugin_basename(__FILE__)) . '/lang');
}
add_action('plugins_loaded', __NAMESPACE__ . '\\load_textdomain');

/***********************************************************************
 * Ajax - this hook must be called in the main plugin file
 * *********************************************************************/
add_action( 'wp_ajax_title_check', __NAMESPACE__ . '\\Enabled\ajax_callback' );

/***********************************************************************
 * Simple Logging when WP_DEBUG_LOG == true
 * *********************************************************************/
function write_log ( $log )  {
    if ( true === WP_DEBUG_LOG ) {
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}
