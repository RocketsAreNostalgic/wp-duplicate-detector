<?php
namespace OrionRush\DuplicateDetector\Activation;

if ( ! defined( 'ABSPATH' ) ) die();

/**
 * Check for minimum operating requirements
 *
 * @param string $wpv - Minimum WP Version
 * @param string $phpv - Minimum PHP Version
 */
function activate(  $phpv = "5.5",  $wpv = "4.7" )
{
    // Do we have to define these as strings for php7?
    // \OrionRush\DuplicateDetector\write_log($phpv);
    // \OrionRush\DuplicateDetector\write_log($wpv);

    // We've not tested this below WP 4.7

    $flag = null;
    $current = null;
    $target_version = null;
    $wp_version = get_bloginfo('version');

    if ( version_compare( PHP_VERSION, $phpv, '<' ) )
    {
        $flag = 'PHP';
        $current_version = PHP_VERSION;
        $target_version = $phpv;
    }
    if ( version_compare( $wp_version, $wpv, '<' ) )
    {
        $flag = 'WordPress';
        $current_version = $wp_version;
        $target_version = $wpv;

    }

    if ($flag !== null) {

        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $name = DD_PLUGIN_NAME;
        $format = __('Sorry, <strong>%s</strong> requires %s version %s or greater. <br/> You are currently running version: %s');

        wp_die(sprintf($format, $name, $flag, $target_version, $current_version), 'Plugin Activation Error', array('response' => 500, 'back_link' => TRUE));
        deactivate_plugins( plugin_basename(DD_PLUGIN) );
    }
    else if (get_option('orionrush_duplicate_detector') === false)
    {
        add_option('orionrush_duplicate_detector', \OrionRush\DuplicateDetector\Admin\get_defaults());
    }
    return;
}
