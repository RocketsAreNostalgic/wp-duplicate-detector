<?php
namespace OrionRush\DuplicateDetector;

if ( ! defined( 'ABSPATH' ) ) die();

/*
Plugin Name:   Duplicate Detector
Plugin URI:    https://orionrush.com
Description:   Add lightweight plugin to help prevent post title duplication.
Version:       0.0.1
Author:        orion rush
Text Domain:   orionrush_duplicate_detector
Author URI:    https://orionrush.com/
License:       MIT License
License URI:   http://opensource.org/licenses/MIT
*/

if (!class_exists('phpUri')) {
    require_once(__DIR__ . '/vendor/phpuri.php');
}
define('DUPLICATE_DETECTOR_PATH', plugin_dir_path(__FILE__));
define('DUPLICATE_DETECTOR_FOLDER', __FILE__);

require_once(__DIR__ . '/lib/admin.php');
require_once(__DIR__ . '/lib/detector.php');

function activation() {
    require_once(DUPLICATE_DETECTOR_PATH . 'lib/activation.php');
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\activation');

function load_textdomain() {
    load_plugin_textdomain('duplicate_detector', false, dirname(plugin_basename(__FILE__)) . '/lang');
}
add_action('plugins_loaded', __NAMESPACE__ . '\\load_textdomain');


/***********************************************************************
 * Ajax - this hook must be called in the main plugin file
 * *********************************************************************/
add_action( 'wp_ajax_title_check', __NAMESPACE__ . '\\Enabled\duplicate_detector_callback' );