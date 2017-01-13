<?php
namespace OrionRush\DuplicateDetector\Activation;

if ( ! defined( 'ABSPATH' ) ) die();

/**
 * Check for minimum operating requirements
 * We've not tested this below WP 4.7
 * @param string $wpv - Minimum WP Version
 * @param string $phpv - Minimum PHP Version
 */
function activate($blah, $phpv = "5.6", $wpv = "4.7")
{
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
