<?php
namespace OrionRush\DuplicateDetector\Admin;
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( is_admin() ) {
	add_action( 'admin_menu', __NAMESPACE__ . '\\add_admin_menu' );
	add_action( 'admin_init', __NAMESPACE__ . '\\register_settings_init' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets' );
} else {
	return;
}

function add_admin_menu() {
	if ( current_user_can( "manage_options" ) ) { // we cant check for this sooner
		$settings_page = add_options_page( 'Duplicate Detector', 'Duplicate Detector', 'manage_options', 'orionrush_duplicate_detector', __NAMESPACE__ . '\\options_page' );
		add_action( 'load-' . $settings_page, __NAMESPACE__ . '\\load_admin_assets' );
	}
}

function load_admin_assets() {
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets' );
}

function enqueue_admin_assets() {
	// We currently have no additional style or scripts
	//  wp_enqueue_style('orionrush-duplicate-detector-admin', plugins_url('/assets/styles/admin.css', DD_DIR), array());
	//  wp_enqueue_script('orionrush-duplicate-detector-admin', plugins_url('/assets/scripts/admin-min.js', DD_DIR), array('jquery-ui-sortable'));
}

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

function get_defaults() {
	return array(
		'post_types' => array( 'post', 'page' )
	);
}

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

function settings_sanitize( $input ) {
	$output = array(
		'post_types' => array()
	);
	if ( isset( $input['post_types'] ) ) {
		$post_types = get_post_types();
		foreach ( (array) $input['post_types'] as $post_type ) {
			if ( array_key_exists( $post_type, $post_types ) ) {
				$output['post_types'][] = $post_type;
			}
		}
	}

	return $output;
}

function control_post_types() {
	$key      = 'post_types';
	$settings = get_settings();
	$saved    = get_setting( $key );
	$message  = __( "Select which post types Duplicate Detector should work with.", 'dupdetect' );
	print "\n" . '<em></em>' . $message . '<br/><br/>';
	print "\n" . '<fieldset>';
	foreach ( get_post_types( array( 'public' => true ) ) as $post_type => $label ) {
		$id      = 'orionrush_duplicate_detector_' . $key . '_' . $post_type;
		$checked = ( in_array( $post_type, $saved ) ) ? ' checked="checked"' : '';
		$object  = get_post_type_object( $label );
		$label   = $object->labels->name;
		print "\n" . '<label for="' . esc_attr( $id ) . '"><input' . $checked . ' id="' . esc_attr( $id ) . '" type="checkbox" name="orionrush_duplicate_detector[' . $key . '][]" value="' . esc_attr( $post_type ) . '"> ' . ucwords( esc_html( $label ) ) . '</label><br>';
	}
	print "\n" . '</fieldset>';
}