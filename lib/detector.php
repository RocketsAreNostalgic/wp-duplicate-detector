<?php
namespace OrionRush\DuplicateDetector\Enabled;

if ( ! defined( 'ABSPATH' ) ) die();
if (!is_admin()){ return; }

// Load search term filter an sql query generator
require_once(__DIR__ . '/search-term-filter.php');
require_once(__DIR__ . '/sql-query-generator.php');

/**
 * Enqueues styles and scripts onto post types if they have been enabled in the admin settings page.
 *
 * @since       0.0.1
 * @author      orionrush
 */
function enqueue_dd_in_admin() {
    $settings = \OrionRush\DuplicateDetector\Admin\get_settings();
    if (empty($settings['post_types'])) {
      return;
    }
    $dd_debug = 'FALSE';
    if (WP_DEBUG) {
        $dd_debug = 'TRUE';
    }
    if (in_array(get_post_type(), $settings['post_types'])) {
        wp_enqueue_style('orionrush-duplicate-detector', plugins_url('/assets/styles/duplicate-detector.css', DUPLICATE_DETECTOR_FOLDER), array());
        wp_enqueue_script('orionrush-duplicate-detector', plugins_url('/assets/scripts/duplicate-detector-min.js', DUPLICATE_DETECTOR_FOLDER), array('jquery'), true);
        wp_localize_script( 'orionrush-duplicate-detector', 'object_DD', array(
            'button_notice'  => esc_html__('Check for duplicate post titles.', 'orionrush_duplicate_detector'),
            'error_message'  => esc_html__('Hey! We received an error:', 'orionrush_duplicate_detector'),
            'debug' => $dd_debug
            )
        );
    }
    return $settings;
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_dd_in_admin');

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

function ajax_callback()
{
    $dupes_found_head_text = __('Whoa there! We found the following entries with a similar heading:');
    $dupes_found_foot_text = __('The title(s) listed above look very similar to this one. Consider making your title more specific, or perhaps move it to the trash. </br> Also pay attention to your permalink for good SEO.');
    $confirmation_text = __('This Venue title looks unique!');

    global $wpdb;
    $title = $_POST['post_title'];
    $post_id = $_POST['post_id'];
    $sim_query = "SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND post_title LIKE '%%%s%%' AND ID != '%d'";
    $sim_results = $wpdb->get_results( $wpdb->prepare( $sim_query, $wpdb->esc_like($title), $post_id ) );
    if ($sim_results)
    {
        $notice = array("head" => $dupes_found_head_text, "foot" =>"$dupes_found_foot_text");
        foreach ($sim_results as $sim_result)
        {
            $details['title'] = get_the_title($sim_result->ID);
            $path = 'post.php?post=' . $sim_result->ID . '&action=edit';
            $details['link'] = esc_url(admin_url($path));
            // $details['city'] =  wpcf_api_field_meta_value( 'city', $sim_result->ID );
            $details['ID'] =  $sim_result->ID;
            $posts[] = $details;
        }
        $return_json = array("status" => "true", "notice" => $notice, "posts"=>$posts );
    }
    else
    {
        $return_json = array("status" => "false", "notice" => $confirmation_text);
    }
    if( ob_get_length() )
        ob_clean();
    header('Content-Type: application/json');
    // Flush buffers to default point http://php.net/manual/en/function.exit.php#101204
    exit(json_encode($return_json));
}