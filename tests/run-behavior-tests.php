<?php
/**
 * Lightweight WordPress behavior harness.
 */

define( 'ABSPATH', __DIR__ . '/wordpress/' );

$ko_test_options         = array();
$ko_test_actions         = array();
$ko_test_filters         = array();
$ko_test_scripts         = array();
$ko_test_inline_scripts  = array();
$ko_test_settings_errors = array();
$ko_test_failures        = array();
$ko_test_is_multisite    = false;

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

function update_option( $key, $value, $autoload = null ) {
	global $ko_test_options;
	unset( $autoload );
	$ko_test_options[ $key ] = $value;
	return true;
}

function is_multisite() {
	return (bool) $GLOBALS['ko_test_is_multisite'];
}

function get_site_option( $key, $default = false ) {
	return get_option( 'site:' . $key, $default );
}

function wp_check_invalid_utf8( $value ) {
	return $value;
}

function wp_unslash( $value ) {
	return $value;
}

function sanitize_key( $value ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) );
}

function sanitize_text_field( $value ) {
	return trim( strip_tags( (string) $value ) );
}

function sanitize_email( $value ) {
	$sanitized = filter_var( (string) $value, FILTER_SANITIZE_EMAIL );
	return filter_var( $sanitized, FILTER_VALIDATE_EMAIL ) ? $sanitized : '';
}

function esc_url_raw( $value ) {
	$value = trim( (string) $value );
	return preg_match( '#^https?://#i', $value ) ? $value : '';
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
$activated = $ko_test_options['ko_accessiyes_css_customizations'] ?? null;
ko_test_assert( is_array( $activated ), 'Activation did not add the settings array.' );
ko_test_assert( '' === ( $activated['script_snippet'] ?? null ), 'New activation did not start with a blank script field.' );
ko_test_assert( 767 === ( $activated['breakpoints']['mobile_max'] ?? null ), 'Default mobile breakpoint is incorrect.' );
ko_test_assert( 1024 === ( $activated['breakpoints']['tablet_max'] ?? null ), 'Default tablet breakpoint is incorrect.' );

$activated['appearance']['label'] = 'Preserved label';
$ko_test_options['ko_accessiyes_css_customizations'] = $activated;
KO_AccessiYES_CSS_Customizations::activate();
ko_test_assert( 'Preserved label' === $ko_test_options['ko_accessiyes_css_customizations']['appearance']['label'], 'Reactivation overwrote saved settings.' );

$ko_test_options['ko_accessiyes_css_customizations'] = '.legacy { color: red; }';
KO_AccessiYES_CSS_Customizations::activate();
$migrated = $ko_test_options['ko_accessiyes_css_customizations'];
ko_test_assert( is_array( $migrated ), 'Legacy CSS was not migrated to the settings array.' );
ko_test_assert( '.legacy { color: red; }' === $migrated['advanced_css'], 'Legacy CSS was not preserved in Advanced CSS.' );
ko_test_assert( '' === $migrated['script_snippet'], 'Legacy migration invented a site-specific script.' );

$plugin    = new KO_AccessiYES_CSS_Customizations();
$widget_id = '8269f6cd-fd39-4411-b6c6-9d804bfd4584';
$config    = array(
	'status'       => array( 'mobile' => false, 'desktop' => true ),
	'iconId'       => 'wheel',
	'iconSize'     => 64,
	'label'        => '<b>Open accessibility tools</b>',
	'heading'      => 'Accessibility controls',
	'position'     => array( 'mobile' => 'top-left', 'desktop' => 'top-right' ),
	'margins'      => array(
		'mobile'  => array( 'top' => 11, 'bottom' => 12, 'left' => 13, 'right' => 14 ),
		'desktop' => array( 'top' => 21, 'bottom' => 22, 'left' => 23, 'right' => 24 ),
	),
	'language'     => array( 'default' => 'es', 'selected' => array( 'fr', 'xx' ) ),
	'primaryColor' => '#123abc',
	'keyboard'     => array( 'enabled' => true, 'shortcut' => 'alt+a' ),
	'modules'      => array(
		'content'   => array( 'adjustFontSizing' => array( 'enabled' => false ) ),
		'statement' => array(
			'enabled'         => true,
			'url'             => 'https://example.test/accessibility/',
			'displayInWidget' => true,
			'generatedDate'   => '2026-08-27',
			'formData'        => array(
				'companyName'       => 'Example Company',
				'businessEmail'     => 'access@example.test',
				'website'           => 'https://example.test',
				'wcagStandard'      => 'WCAG 2.2 Level AA',
				'conformanceStatus' => 'not-assessed',
			),
		),
	),
);
$snippet = '<script>(function(w,d,s,u,o){w._cyA11yConfig=' . json_encode( $config ) . ';var js=d.createElement(s);js.src=u;})(window,document,"script","https://cdn-cookieyes.com/widgets/accessibility.js?id=' . $widget_id . '");</script>';

$expected_url = 'https://cdn-cookieyes.com/widgets/accessibility.js?id=' . $widget_id;
ko_test_assert( $expected_url === KO_AccessiYES_CSS_Customizations::extract_script_url( $snippet ), 'Supported CDN URL was not extracted.' );
ko_test_assert( '' === KO_AccessiYES_CSS_Customizations::extract_script_url( '<script src="https://evil.test/widgets/accessibility.js?id=' . $widget_id . '"></script>' ), 'Unapproved CDN host was accepted.' );
ko_test_assert( '' === KO_AccessiYES_CSS_Customizations::extract_script_url( '<script src="http://cdn-cookieyes.com/widgets/accessibility.js?id=' . $widget_id . '"></script>' ), 'Insecure CDN URL was accepted.' );
$extracted_config = KO_AccessiYES_CSS_Customizations::extract_script_config( $snippet );
ko_test_assert( 'wheel' === ( $extracted_config['iconId'] ?? null ), 'Generator JSON was not extracted.' );
ko_test_assert( array() === KO_AccessiYES_CSS_Customizations::extract_script_config( "window._cyA11yConfig={iconId:'wheel'};var js=1;" ), 'Invalid JSON configuration was accepted.' );

$ko_test_settings_errors = array();
$clean = $plugin->sanitize_css( "<style>.test { color: red; }</style>\0" );
ko_test_assert( '.test { color: red; }' === $clean, 'CSS sanitizer did not remove style tags and null bytes.' );
ko_test_assert( 1 === count( $ko_test_settings_errors ), 'CSS sanitizer did not add one warning.' );

$ko_test_options['ko_accessiyes_css_customizations'] = KO_AccessiYES_CSS_Customizations::get_default_settings();
$input = KO_AccessiYES_CSS_Customizations::get_default_settings();
$input['script_snippet']        = $snippet;
$input['import_script_settings'] = true;
$input['breakpoints']            = array( 'mobile_max' => 600, 'tablet_max' => 1100 );
$input['appearance']['inner_icon_size'] = 99;
$input['rings'] = array(
	'inner_enabled' => false,
	'inner_width'   => 20,
	'inner_color'   => '#fff',
	'outer_enabled' => false,
	'outer_width'   => 30,
	'outer_color'   => '#abcdef',
);
$sanitized = $plugin->sanitize_settings( $input );
ko_test_assert( $snippet === $sanitized['script_snippet'], 'Full generator snippet was not retained as an administrator setting.' );
ko_test_assert( 'wheel' === $sanitized['appearance']['icon_id'], 'Script icon setting was not imported.' );
ko_test_assert( 64 === $sanitized['appearance']['icon_size'], 'Script icon size was not imported.' );
ko_test_assert( '#123ABC' === $sanitized['appearance']['primary_color'], 'Script primary colour was not imported and normalized.' );
ko_test_assert( 'Open accessibility tools' === $sanitized['appearance']['label'], 'Imported label was not sanitized.' );
ko_test_assert( 64 === $sanitized['appearance']['inner_icon_size'], 'Inner icon size was not clamped.' );
ko_test_assert( false === $sanitized['status']['mobile'] && true === $sanitized['status']['desktop'], 'Device status was not imported.' );
ko_test_assert( 'top-right' === $sanitized['position']['tablet']['corner'], 'Tablet did not inherit imported desktop position.' );
ko_test_assert( 21 === $sanitized['position']['tablet']['vertical'], 'Tablet did not inherit the active desktop margin.' );
ko_test_assert( 600 === $sanitized['breakpoints']['mobile_max'] && 1100 === $sanitized['breakpoints']['tablet_max'], 'Custom breakpoints were not retained.' );
ko_test_assert( false === $sanitized['rings']['inner_enabled'] && 12 === $sanitized['rings']['inner_width'], 'Inner ring settings were not sanitized.' );
ko_test_assert( false === $sanitized['rings']['outer_enabled'] && 20 === $sanitized['rings']['outer_width'], 'Outer ring settings were not sanitized.' );
ko_test_assert( '#ABCDEF' === $sanitized['rings']['outer_color'], 'Outer ring colour was not normalized.' );
ko_test_assert( 'es' === $sanitized['language']['default'] && array( 'fr' ) === $sanitized['language']['selected'], 'Language settings were not imported and filtered.' );
ko_test_assert( false === $sanitized['modules']['content']['adjustFontSizing']['enabled'], 'Module status was not imported.' );
ko_test_assert( true === $sanitized['statement']['display_in_widget'], 'Generated statement status was not imported.' );
ko_test_assert( 'Example Company' === $sanitized['statement']['form_data']['company_name'], 'Generated statement company was not imported.' );
ko_test_assert( 'not-assessed' === $sanitized['statement']['form_data']['conformance_status'], 'Generated statement conformance was not imported.' );

$invalid_input = KO_AccessiYES_CSS_Customizations::get_default_settings();
$invalid_input['script_snippet'] = '<script>alert(1)</script>';
$invalid_input['breakpoints'] = array( 'mobile_max' => 10, 'tablet_max' => 10 );
$invalid_input['appearance']['primary_color'] = 'red';
$invalid_input['statement']['form_data']['business_email'] = 'not an email';
$invalid_input['statement']['form_data']['conformance_status'] = 'invented';
$ko_test_settings_errors = array();
$invalid = $plugin->sanitize_settings( $invalid_input );
ko_test_assert( 320 === $invalid['breakpoints']['mobile_max'] && 321 === $invalid['breakpoints']['tablet_max'], 'Breakpoint bounds were not enforced.' );
ko_test_assert( '#1863DC' === $invalid['appearance']['primary_color'], 'Invalid primary colour did not use the fallback.' );
ko_test_assert( '' === $invalid['statement']['form_data']['business_email'], 'Invalid statement email was not rejected.' );
ko_test_assert( 'fully-conformant' === $invalid['statement']['form_data']['conformance_status'], 'Invalid conformance status was not rejected.' );
ko_test_assert( in_array( 'ko_accessiyes_invalid_script', array_column( $ko_test_settings_errors, 'code' ), true ), 'Invalid script did not add a settings error.' );

$incomplete_statement = KO_AccessiYES_CSS_Customizations::get_default_settings();
$incomplete_statement['statement']['display_in_widget'] = true;
$incomplete_statement['statement']['form_data']['company_name'] = 'Example Company';
$ko_test_settings_errors = array();
$incomplete_statement = $plugin->sanitize_settings( $incomplete_statement );
ko_test_assert( false === $incomplete_statement['statement']['display_in_widget'], 'Incomplete generated statement remained enabled.' );
ko_test_assert( in_array( 'ko_accessiyes_statement_incomplete', array_column( $ko_test_settings_errors, 'code' ), true ), 'Incomplete generated statement did not add a warning.' );

$managed = KO_AccessiYES_CSS_Customizations::get_default_settings();
$managed['script_snippet'] = $snippet . '</script><script>alert("do not forward")</script>';
$managed['appearance']['inner_icon_size'] = 30;
$managed['statement']['display_in_widget'] = true;
$managed['statement']['generated_date'] = '2026-08-27';
$managed['statement']['form_data'] = array(
	'company_name'       => 'Example Company',
	'business_email'     => 'access@example.test',
	'website'            => 'https://example.test',
	'wcag_standard'      => 'WCAG 2.2 Level AA',
	'conformance_status' => 'not-assessed',
);
$managed['advanced_css'] = '</script>.safe { color: green; }';
$ko_test_options['ko_accessiyes_css_customizations'] = $managed;
$ko_test_options['active_plugins'] = array();
$ko_test_scripts = array();
$ko_test_inline_scripts = array();
$plugin->enqueue_frontend_script();
ko_test_assert( isset( $ko_test_scripts['ko-accessiyes-css-customizations'] ), 'Valid managed script did not enqueue the loader.' );
ko_test_assert( true === $ko_test_scripts['ko-accessiyes-css-customizations']['in_footer'], 'Managed loader was not placed in the footer.' );
ko_test_assert( isset( $ko_test_inline_scripts['ko-accessiyes-css-customizations'] ), 'Manager configuration was not added.' );
$inline = $ko_test_inline_scripts['ko-accessiyes-css-customizations'];
ko_test_assert( 'before' === $inline['position'], 'Manager configuration must load before the local loader.' );
$inline_json = preg_replace( '/^window\.KOAccessiYESManager\s*=\s*/', '', rtrim( $inline['data'], ';' ) );
$inline_config = json_decode( $inline_json, true );
ko_test_assert( $expected_url === ( $inline_config['scriptUrl'] ?? null ), 'Allowlisted CDN URL is missing from front-end configuration.' );
ko_test_assert( false === strpos( $inline['data'], 'do not forward' ), 'Arbitrary pasted script content reached the front end.' );
ko_test_assert( false === strpos( $inline['data'], '</script>' ), 'Inline manager configuration contains an unsafe closing script tag.' );
ko_test_assert( false !== strpos( $inline['data'], '\\u003C' ), 'Inline manager configuration did not hex-encode opening angle brackets.' );
ko_test_assert( false !== strpos( $inline['data'], 'statementTemplates' ), 'Generated statement template was not supplied when enabled.' );

$ko_test_options['active_plugins'] = array( 'accessibility-widget/accessibility-widget.php' );
$ko_test_scripts = array();
$ko_test_inline_scripts = array();
$plugin->enqueue_frontend_script();
ko_test_assert( isset( $ko_test_scripts['ko-accessiyes-css-customizations'] ), 'Official plugin mode did not preserve the Shadow DOM style bridge.' );
$style_only_json = preg_replace( '/^window\.KOAccessiYESManager\s*=\s*/', '', rtrim( $ko_test_inline_scripts['ko-accessiyes-css-customizations']['data'], ';' ) );
$style_only_config = json_decode( $style_only_json, true );
ko_test_assert( false === ( $style_only_config['loadVendor'] ?? null ), 'Official plugin mode did not disable the vendor loader.' );
ko_test_assert( '' === ( $style_only_config['scriptUrl'] ?? null ), 'Official plugin mode exposed a second vendor-script URL.' );
ko_test_assert( true === $plugin->is_accessiyes_active(), 'Official AccessiYES plugin was not detected.' );

$ko_test_options['active_plugins'] = array();
$managed['status'] = array( 'desktop' => false, 'tablet' => false, 'mobile' => false );
$ko_test_options['ko_accessiyes_css_customizations'] = $managed;
$ko_test_scripts = array();
$plugin->enqueue_frontend_script();
ko_test_assert( empty( $ko_test_scripts ), 'All-disabled device status still enqueued the widget.' );

$css_settings = KO_AccessiYES_CSS_Customizations::get_default_settings();
$css_settings['appearance']['icon_size'] = 64;
$css_settings['appearance']['inner_icon_size'] = 32;
$css_settings['rings']['inner_enabled'] = false;
$css_settings['rings']['outer_enabled'] = false;
$css_settings['status']['mobile'] = false;
$css_settings['position']['desktop'] = array( 'corner' => 'top-left', 'horizontal' => 17, 'vertical' => 19 );
$shadow_css = $plugin->build_shadow_css( $css_settings );
ko_test_assert( false !== strpos( $shadow_css, 'border: 0 !important' ), 'Disabled inner ring did not remove the border.' );
ko_test_assert( false !== strpos( $shadow_css, 'box-shadow: 0 5px 15px #252c6126, 0 2px 4px #5d649433 !important' ), 'Disabled outer ring did not remove the normal solid ring shadow.' );
ko_test_assert( false !== strpos( $shadow_css, '0 5px 15px #252c6126' ), 'Depth shadow was not retained.' );
ko_test_assert( false !== strpos( $shadow_css, 'outline-offset: 3px !important' ), 'Focus outline offset was not preserved without an outer ring.' );
ko_test_assert( false !== strpos( $shadow_css, '@media (prefers-contrast: high)' ), 'High-contrast preference protection is missing.' );
ko_test_assert( false !== strpos( $shadow_css, 'width: 32px !important' ), 'Inner icon width was not generated.' );
ko_test_assert( false !== strpos( $shadow_css, '@media (max-width: 767px)' ), 'Mobile media query is missing.' );
ko_test_assert( false !== strpos( $shadow_css, '@media (min-width: 768px) and (max-width: 1024px)' ), 'Tablet media query is missing.' );
ko_test_assert( false !== strpos( $shadow_css, '.cya11y-menu,' ), 'Disabled breakpoint does not hide an open menu.' );
ko_test_assert( false !== strpos( $shadow_css, "left: 17px !important" ), 'Desktop launcher side was not generated.' );
ko_test_assert( false !== strpos( $shadow_css, "left: 0 !important" ), 'Responsive menu side was not generated.' );

$widget_config = $plugin->build_widget_config( $managed );
ko_test_assert( isset( $widget_config['modules']['content']['textAlign'] ), 'Widget configuration omitted a managed module.' );
ko_test_assert( true === $widget_config['modules']['statement']['displayInWidget'], 'Widget configuration omitted generated statement status.' );
ko_test_assert( 'Example Company' === $widget_config['modules']['statement']['formData']['companyName'], 'Widget configuration omitted generated statement data.' );

$links = $plugin->add_plugin_action_links( array( '<a href="#">Deactivate</a>' ) );
ko_test_assert( false !== strpos( $links[0], 'options-general.php?page=ko-accessiyes-css-customizations' ), 'Settings action link is incorrect.' );

if ( ! empty( $ko_test_failures ) ) {
	fwrite( STDERR, "FAILED\n- " . implode( "\n- ", $ko_test_failures ) . "\n" );
	exit( 1 );
}

echo "All WordPress behavior checks passed.\n";
