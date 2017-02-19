<?php
namespace OrionRush\DuplicateDetector\Admin;
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Wire up the options page via relevant action hooks
 * Add the settings page to the menu
 * Register the plugin settings
 * Load up the scripts
 *
 * @since 0.0.3
 * @author orionrush
 */

if ( is_admin() ) {
	add_action( 'admin_menu', __NAMESPACE__ . '\\add_admin_menu' );
	add_action( 'admin_init', __NAMESPACE__ . '\\register_settings_init' );
	//add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets' ); // redundant? also called by admin_enqueue_scripts
} else {
	return;
}

/**
 * Adds the option page if the user has the privileges to manage options.
 * Called by admin_menu hook
 * Calls function options_page, which has markup for page
 * Calls via hook, load_admin_assets for css and js
 *
 * @since 0.0.3
 * @author orionrush
 */
function add_admin_menu() {
	if ( current_user_can( "manage_options" ) ) { // we cant check for this sooner
		$settings_page = add_options_page( 'Duplicate Detector', 'Duplicate Detector', 'manage_options', 'orionrush_duplicate_detector', __NAMESPACE__ . '\\options_page' );
		add_action( 'load-' . $settings_page, __NAMESPACE__ . '\\load_admin_assets' );
	}
}

/**
 * Add enqueue_admin_assets function to admin_enqueue_scripts action hook
 * Called by action in add_admin_menu
 *
 * @since 0.0.3
 * @author orionrush
 */
function load_admin_assets() {
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets' );
}

/**
 * Enqueue styles and scripts
 * Called by load_admin_assets, on the admin_enqueue_scripts hook
 * Called by the wp_enqueue_scripts action hook in page top (redundant?)
 *
 * @since 0.0.3
 * @author orionrush
 */
function enqueue_admin_assets() {
	wp_enqueue_style( 'orionrush-duplicate-detector-admin', plugins_url( '/assets/styles/admin.css', DD_DIR ), array() );
	//  wp_enqueue_script('orionrush-duplicate-detector-admin', plugins_url('/assets/scripts/admin-min.js', DD_DIR), array('jquery-ui-sortable'));
}

/**
 * Registers the settings, section(s) and field settings, each with its own callback.
 * Called by hook to admin_init
 *
 * @since 0.0.3
 * @author orionrush
 */
function register_settings_init() {
	register_setting(
		'orionrush_duplicate_detector',
		'orionrush_duplicate_detector',
		__NAMESPACE__ . '\\settings_sanitize'
	);

	add_settings_section(
		'orionrush_duplicate_detector_site_integration',
		__( 'Site Integration', 'orionrush_duplicate_detector' ),
		'__return_false',
		'orionrush_duplicate_detector'
	);
	add_settings_field(
		'orionrush_duplicate_detector_post_types',
		__( 'Post Types', 'orionrush_duplicate_detector' ),
		__NAMESPACE__ . '\\control_post_types',
		'orionrush_duplicate_detector',
		'orionrush_duplicate_detector_site_integration'
	);
}

/**
 * Adds the basic html for the options page.
 * Called by add_admin_menu which also hooks into the load action for the settings page to enqueue scripts.
 *
 * @since 0.0.3
 * @author orionrush
 */
function options_page() { ?>
    <div class="wrap">
        <form action="options.php" method="POST">
            <h2>Duplicate Detector Settings</h2>
			<?php
			settings_fields( 'orionrush_duplicate_detector' );
			do_settings_sections( 'orionrush_duplicate_detector' );
			submit_button();
			?>
        </form>
    </div>
	<?php
}

/**
 * Sets up the default values for the plugin on first load.
 * Called by get_settings if option is empty.
 *
 * @since 0.0.3
 * @author orionrush
 *
 * @return array
 */
function get_defaults() {
	return array(
		'post_types'         => array( 'post', 'page' ),
		'post_types_isolate' => array()
	);
}

/**
 * Gets the current settings for the plugin
 * If the option has not yet been set, it applies the defaults.
 *
 * @since 0.0.3
 * @author orionrush
 *
 * @return array
 */
function get_settings() {
	return wp_parse_args( (array) get_option( 'orionrush_duplicate_detector' ), get_defaults() );
}

function get_setting( $key ) {

	$settings = get_settings();

	if ( isset( $settings[ $key ] ) ) {
		return $settings[ $key ];
	}

	return false;
}

/**
 * Get public posts types that DD can work with.
 *
 * @since 0.0.3
 * @author orionrush
 *
 * @return array
 */
function get_public_post_types() {

	$post_types = get_post_types( array( 'public' => true ) );

	// remove media attachments from the list as DD wont work on them.
	$remove = array_search( 'attachment', $post_types );
	if ( $remove !== false ) {
		unset( $post_types[ $remove ] );
	}

	return $post_types;
}

/**
 * Sanitizes the incoming options prior to save to the database.
 * Called by register_settings_init and the register_setting hook.
 *
 * @since 0.0.3
 * @author orionrush
 *
 * @param $input
 *
 * @return array
 */
function settings_sanitize( $input ) {
	$output = array(
		'post_types'         => array(),
		'post_types_isolate' => array()

	);
	// need to add second checkbox for post_type_isolate
	// if post_type checkbox is selected, check if isolated is too?
	// no these two lines need to be separate queries.
	if ( isset( $input['post_types'] ) ) {
		$post_types = get_public_post_types();
		foreach ( (array) $input['post_types'] as $post_type ) {
			if ( array_key_exists( $post_type, $post_types ) ) {
				$output['post_types'][] = $post_type;
			}
		}
	}
	if ( isset( $input['post_types_isolate'] ) ) {
		// Create an array of possible post_types that require isolation
		$post_types_to_isolate = get_public_post_types();

		foreach ( (array) $input['post_types_isolate'] as $post_type_to_isolate ) {
			if ( array_key_exists( $post_type_to_isolate, $post_types_to_isolate ) ) {
				$output['post_types_isolate'][] = $post_type_to_isolate;
			}
		}
	}

	\OrionRush\DuplicateDetector\Helpers\write_log( 'sanitized plugin options array:' );
	\OrionRush\DuplicateDetector\Helpers\write_log( $output );

	return $output;
}

/**
 * Prints the list of options to select post-types DD will appear on,
 * and optionally isolate searches within post-types to their own kind.
 *
 * @since 0.0.3
 * @author orionrush
 */
function control_post_types() {

	$key           = 'post_types';
	$saved         = get_setting( 'post_types' );
	$saved_isolate = get_setting( 'post_types_isolate' );

	$message1     = __( "Select which post types Duplicate Detector should work with.", 'orionrush_duplicate_detector' );
	$message2     = __( "Activate | Isolated search", 'orionrush_duplicate_detector' );
	$description1 = __( "Include DD on post-type", 'orionrush_duplicate_detector' );
	$description2 = __( "Isolate duplicate title searches to this type only.", 'orionrush_duplicate_detector' );
	$footer = sprintf(__( "Enable DD search on available post types above. %s By enabling isolated search on a post-type, duplicate post-type searches from that post-type will be restricted to that type only. %s Likewise, results from isolated post-types, are excluded from wider duplicate post title search results.", 'orionrush_duplicate_detector' ), '<br/>','<br/>');

	print "\n" . $message1 . '<br/><br/>';
	print "\n" . '<fieldset class="grid">';
	print "\n" . '<div class="col"><em>' . $message2 . '</em></div>';

	$post_types = get_public_post_types();

	foreach ( $post_types as $post_type => $label ) {
		$id              = 'orionrush_duplicate_detector_' . $key . '_' . $post_type;
		$checked         = ( in_array( $post_type, $saved ) ) ? ' checked="checked"' : '';
		$checked_isolate = ( in_array( $post_type, $saved_isolate ) ) ? ' checked="checked"' : '';
		$object          = get_post_type_object( $label );
		$label           = $object->labels->name;
		print "\n" . '<div class="col">
		                    <label for="' . esc_attr( $id ) . '" class="thirds">' . ucwords( esc_html( $label ) ) . ': </label> 
		                    <input' . $checked . '          title="' . $description1 . '" id="' . esc_attr( $id ) . '"          type="checkbox" name="orionrush_duplicate_detector[' . $key . '][]"         value="' . esc_attr( $post_type ) . '"  class="thirds"></input> 
                            <input' . $checked_isolate . '  title="' . $description2 . '" id="' . esc_attr( $id ) . '_isolate"  type="checkbox" name="orionrush_duplicate_detector[' . $key . '_isolate][]" value="' . esc_attr( $post_type ) . '"  class="thirds"></input>
                      </div>';
	}
	print "\n" . '</fieldset>';
	print "\n" . '<div class="well"><p><em>' . $footer . '<em/></p></div>';
}