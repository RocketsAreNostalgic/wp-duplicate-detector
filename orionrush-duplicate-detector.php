<?php
namespace OrionRush\DuplicateDetector;
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Plugin Name:   Duplicate Detector
 * Description:   Add lightweight plugin to help prevent post or page title duplication.
 * Version:       0.0.3
 * Author:        Ben Rush
 * Author URI:    http://www.orionrush.com
 * Plugin URI:    http://www.rocketsarenostalgic.net
 * License:       GPL
 * License URI:   https://wordpress.org/about/gpl/
 * Text Domain:   orionrush_duplicate_detector
 */


/***********************************************************************
 * Definitions
 * /********************************************************************/
define( 'DD_PLUGIN', __FILE__ ); // The location of this plugin
define( 'DD_PATH', plugin_dir_path( __FILE__ ) );
define( 'DD_DIR', __FILE__ );
define( 'DD_PLUGIN_NAME', "Duplicate Detector" );

/***********************************************************************
 * Includes
 * /********************************************************************/
require_once( __DIR__ . '/lib/activation.php' );
require_once( __DIR__ . '/lib/admin.php' );
require_once( __DIR__ . '/lib/detector.php' );
require_once( __DIR__ . '/lib/helpers.php' );

/**
 * Load the activation script
 *
 * @since       0.0.2
 * @author      orionrush
 *
 */
register_activation_hook( __FILE__, __NAMESPACE__ . '\\Activation\\activate' );

/**
 * Load Text Domain for translation
 *
 * since       0.0.2
 * @author      orionrush
 *e
 */
function load_textdomain() {
	load_plugin_textdomain( 'orionrush_duplicate_detector', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_textdomain' );

/***********************************************************************
 * Ajax hook, must be called from main plugin file
 * *********************************************************************/
add_action( 'wp_ajax_title_check', __NAMESPACE__ . '\\Enabled\ajax_callback' );