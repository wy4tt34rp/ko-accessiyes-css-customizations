<?php
/**
 * Lightweight WordPress behavior harness.
 */

define( 'ABSPATH', __DIR__ . '/wordpress/' );

$ko_test_options        = array();
$ko_test_actions        = array();
$ko_test_filters        = array();
$ko_test_scripts        = array();
$ko_test_inline_scripts = array();
$ko_test_settings_errors = array();
$ko_test_failures       = array();

function ko_test_assert( $condition, $message ) {
	global $ko_test_failures;

	if ( ! $condition ) {
		$ko_test_failures[] = $message;
	}
}

function plugin_dir_url( $file ) {
	unset( $file );
	return 'https://example.test/wp-content/plugins/ko-accessiyes-css-customizations/';
}

function plugin_basename( $file ) {
	return basename( dirname( $file ) ) . '/' . basename( $file );
}

function add_action( $hook, $callback, $priority = 10 ) {
	global $ko_test_actions;
	$ko_test_actions[] = array( $hook, $callback, $priority );
}

function add_filter( $hook, $callback ) {
	global $ko_test_filters;
	$ko_test_filters[] = array( $hook, $callback );
}

function register_activation_hook( $file, $callback ) {
	unset( $file );
	$GLOBALS['ko_test_activation_hook'] = $callback;
}

function get_option( $key, $default = false ) {
	global $ko_test_options;
	return array_key_exists( $key, $ko_test_options ) ? $ko_test_options[ $key ] : $default;
}

function add_option( $key, $value, $deprecated = '', $autoload = null ) {
	global $ko_test_options;
	unset( $deprecated, $autoload );

	if ( array_key_exists( $key, $ko_test_options ) ) {
		return false;
	}

	$ko_test_options[ $key ] = $value;
	return true;
}

function wp_check_invalid_utf8( $value ) {
	return $value;
}

function add_settings_error( $setting, $code, $message, $type ) {
	global $ko_test_settings_errors;
	$ko_test_settings_errors[] = compact( 'setting', 'code', 'message', 'type' );
}

function wp_enqueue_script( $handle, $src, $dependencies, $version, $in_footer ) {
	global $ko_test_scripts;
	$ko_test_scripts[ $handle ] = compact( 'src', 'dependencies', 'version', 'in_footer' );
}

function wp_add_inline_script( $handle, $data, $position = 'after' ) {
	global $ko_test_inline_scripts;
	$ko_test_inline_scripts[ $handle ] = compact( 'data', 'position' );
	return true;
}

function wp_json_encode( $value, $flags = 0 ) {
	return json_encode( $value, $flags );
}

function admin_url( $path = '' ) {
	return 'https://example.test/wp-admin/' . ltrim( $path, '/' );
}

function esc_url( $value ) {
	return $value;
}

function esc_html__( $value, $domain = null ) {
	unset( $domain );
	return $value;
}

function __( $value, $domain = null ) {
	unset( $domain );
	return $value;
}

require dirname( __DIR__ ) . '/ko-accessiyes-css-customizations.php';

ko_test_assert( isset( $GLOBALS['ko_test_activation_hook'] ), 'Activation hook was not registered.' );
ko_test_assert( in_array( 'wp_enqueue_scripts', array_column( $ko_test_actions, 0 ), true ), 'Front-end enqueue hook was not registered.' );
ko_test_assert( ! empty( $ko_test_filters ), 'Plugins-screen action-link filter was not registered.' );

KO_AccessiYES_CSS_Customizations::activate();
ko_test_assert( array_key_exists( 'ko_accessiyes_css_customizations', $ko_test_options ), 'Activation did not add the CSS option.' );
ko_test_assert( '' === $ko_test_options['ko_accessiyes_css_customizations'], 'New activation did not start blank.' );

$ko_test_options['ko_accessiyes_css_customizations'] = '.saved { color: red; }';
KO_AccessiYES_CSS_Customizations::activate();
ko_test_assert( '.saved { color: red; }' === $ko_test_options['ko_accessiyes_css_customizations'], 'Reactivation overwrote existing CSS.' );

$plugin = new KO_AccessiYES_CSS_Customizations();
$clean  = $plugin->sanitize_css( "<style>.test { color: red; }</style>\0" );
ko_test_assert( '.test { color: red; }' === $clean, 'CSS sanitizer did not remove style tags and null bytes.' );
ko_test_assert( 1 === count( $ko_test_settings_errors ), 'CSS sanitizer did not add one warning.' );

$ko_test_options['ko_accessiyes_css_customizations'] = '';
$plugin->enqueue_frontend_script();
ko_test_assert( empty( $ko_test_scripts ), 'Blank CSS enqueued a front-end script.' );

$ko_test_options['ko_accessiyes_css_customizations'] = '.cya11y-menu-btn { border: 0 !important; }';
$plugin->enqueue_frontend_script();
ko_test_assert( isset( $ko_test_scripts['ko-accessiyes-css-customizations'] ), 'Saved CSS did not enqueue the bridge script.' );
ko_test_assert( true === $ko_test_scripts['ko-accessiyes-css-customizations']['in_footer'], 'Bridge script was not placed in the footer.' );
ko_test_assert( isset( $ko_test_inline_scripts['ko-accessiyes-css-customizations'] ), 'Bridge configuration was not added.' );
ko_test_assert( 'before' === $ko_test_inline_scripts['ko-accessiyes-css-customizations']['position'], 'Bridge configuration must load before the external script.' );
ko_test_assert( false !== strpos( $ko_test_inline_scripts['ko-accessiyes-css-customizations']['data'], '#cya11y-container' ), 'Bridge host selector is missing.' );
ko_test_assert( false !== strpos( $ko_test_inline_scripts['ko-accessiyes-css-customizations']['data'], '.cya11y-menu-btn' ), 'Bridge button selector is missing.' );

$ko_test_options['ko_accessiyes_css_customizations'] = '</script>.safe { color: green; }';
$plugin->enqueue_frontend_script();
$encoded_script = $ko_test_inline_scripts['ko-accessiyes-css-customizations']['data'];
ko_test_assert( false === strpos( $encoded_script, '</script>' ), 'Inline configuration contains an unsafe closing script tag.' );
ko_test_assert( false !== strpos( $encoded_script, '\\u003C' ), 'Inline configuration did not hex-encode the opening angle bracket.' );

$links = $plugin->add_plugin_action_links( array( '<a href="#">Deactivate</a>' ) );
ko_test_assert( false !== strpos( $links[0], 'options-general.php?page=ko-accessiyes-css-customizations' ), 'Settings action link is incorrect.' );

if ( ! empty( $ko_test_failures ) ) {
	fwrite( STDERR, "FAILED\n- " . implode( "\n- ", $ko_test_failures ) . "\n" );
	exit( 1 );
}

echo "All WordPress behavior checks passed.\n";
