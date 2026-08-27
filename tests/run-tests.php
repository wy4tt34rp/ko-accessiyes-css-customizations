<?php
/**
 * Static release checks.
 */

$root     = dirname( __DIR__ );
$required = array(
	'ko-accessiyes-css-customizations.php',
	'assets/js/frontend.js',
	'assets/css/admin.css',
	'assets/images/vela-logo-white.svg',
	'README.md',
	'SOURCE-PROVENANCE.md',
	'readme.txt',
	'uninstall.php',
);
$errors = array();

function ko_accessiyes_assert( $condition, $message ) {
	global $errors;
	if ( ! $condition ) {
		$errors[] = $message;
	}
}

foreach ( $required as $relative_path ) {
	ko_accessiyes_assert( is_file( $root . '/' . $relative_path ), 'Required file is missing: ' . $relative_path );
}

$main_php  = file_get_contents( $root . '/ko-accessiyes-css-customizations.php' );
$frontend  = file_get_contents( $root . '/assets/js/frontend.js' );
$admin_css = file_get_contents( $root . '/assets/css/admin.css' );
$readme    = file_get_contents( $root . '/README.md' );
$provenance = file_get_contents( $root . '/SOURCE-PROVENANCE.md' );
$wp_readme = file_get_contents( $root . '/readme.txt' );
$uninstall = file_get_contents( $root . '/uninstall.php' );

ko_accessiyes_assert( false !== strpos( $main_php, 'Plugin Name: KO: AccessiYES CSS Customizations' ), 'Exact plugin display name is missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'Version: 1.1.0' ), 'Plugin header version is not 1.1.0.' );
ko_accessiyes_assert( false !== strpos( $main_php, "define( 'KO_ACCESSIYES_CSS_VERSION', '1.1.0' )" ), 'Runtime version is not 1.1.0.' );
ko_accessiyes_assert( false === strpos( $main_php, 'Requires Plugins:' ), 'The standalone manager must not require the official plugin.' );
ko_accessiyes_assert( false !== strpos( $wp_readme, 'Stable tag: 1.1.0' ), 'WordPress readme stable tag is not 1.1.0.' );

ko_accessiyes_assert( false !== strpos( $main_php, 'https://www.accessiyes.com/get-your-script/' ), 'Script generator URL is missing from the settings screen.' );
ko_accessiyes_assert( false !== strpos( $readme, 'https://www.accessiyes.com/get-your-script/' ), 'Script generator URL is missing from README.md.' );
ko_accessiyes_assert( false !== strpos( $wp_readme, 'https://www.accessiyes.com/get-your-script/' ), 'Script generator URL is missing from readme.txt.' );
ko_accessiyes_assert( false !== strpos( $main_php, "'script_snippet' => ''" ), 'New installations must start with a blank script field.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'cdn-cookieyes\.com/widgets/accessibility\.js' ), 'Allowlisted AccessiYES CDN extraction is missing.' );
ko_accessiyes_assert( false !== strpos( $frontend, 'window._cyA11yConfig = widgetConfig' ), 'The managed AccessiYES configuration is not installed before the vendor loader.' );
ko_accessiyes_assert( false === strpos( $frontend, 'eval(' ) && false === strpos( $frontend, 'new Function' ), 'The front-end loader must not evaluate arbitrary script text.' );
ko_accessiyes_assert( false !== strpos( $frontend, 'data-ko-accessiyes-managed' ), 'Managed vendor-script marker is missing.' );
ko_accessiyes_assert( false !== strpos( $frontend, 'script[src^="https://cdn-cookieyes.com/widgets/accessibility.js"]' ), 'Duplicate CDN script guard is missing.' );

foreach ( array( 'desktop', 'tablet', 'mobile', 'mobile_max', 'tablet_max' ) as $responsive_key ) {
	ko_accessiyes_assert( false !== strpos( $main_php, "'{$responsive_key}'" ), 'Responsive setting is missing: ' . $responsive_key );
}
foreach ( array( 'inner_enabled', 'inner_width', 'inner_color', 'outer_enabled', 'outer_width', 'outer_color', 'inner_icon_size' ) as $appearance_key ) {
	ko_accessiyes_assert( false !== strpos( $main_php, "'{$appearance_key}'" ), 'Launcher customization is missing: ' . $appearance_key );
}
foreach ( array( 'adjustFontSizing', 'highlightTitle', 'highlightLinks', 'dyslexicFont', 'fontWeight', 'letterSpacing', 'lineHeight', 'textAlign', 'darkContrast', 'lightContrast', 'highContrast', 'highSaturation', 'lowSaturation', 'monochrome', 'readingGuide', 'pauseAnimations', 'bigCursor', 'profiles', 'pageRead', 'muteSounds' ) as $module_key ) {
	ko_accessiyes_assert( false !== strpos( $main_php, "'{$module_key}'" ), 'Official widget module control is missing: ' . $module_key );
}
foreach ( array( 'display_in_widget', 'company_name', 'business_email', 'website', 'wcag_standard', 'conformance_status', 'generated_date' ) as $statement_key ) {
	ko_accessiyes_assert( false !== strpos( $main_php, "'{$statement_key}'" ), 'Statement-generator setting is missing: ' . $statement_key );
}

ko_accessiyes_assert( false !== strpos( $main_php, "strpos( \$plugin_file, 'accessibility-widget/' )" ), 'Official-plugin conflict guard is missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'will not load a second widget' ), 'Official-plugin conflict warning is missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT' ), 'Safe inline JSON flags are missing.' );
ko_accessiyes_assert( false !== strpos( $frontend, 'host.shadowRoot' ), 'Shadow DOM bridge is missing.' );
ko_accessiyes_assert( false !== strpos( $frontend, 'root.appendChild(style)' ), 'Shadow DOM style injection is missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, '.cya11y-menu' ), 'Responsive menu alignment rules are missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'outline-offset' ), 'Managed focus-outline offset is missing.' );

ko_accessiyes_assert( false !== strpos( $readme, 'Why the Shadow DOM Bridge Is Required' ), 'README Shadow DOM explanation is missing.' );
ko_accessiyes_assert( false !== strpos( $readme, 'Official Plugin Conflict Protection' ), 'README conflict-protection section is missing.' );
ko_accessiyes_assert( false !== strpos( $readme, 'independently developed KO/Vela integration' ), 'Independent-integration disclosure is missing.' );
ko_accessiyes_assert( false !== strpos( $readme, 'No AccessiYES plugin code' ), 'No-vendor-code disclosure is missing.' );
ko_accessiyes_assert( false !== strpos( $provenance, 'No source file, compiled asset' ), 'Source-provenance no-vendor-code statement is missing.' );
ko_accessiyes_assert( false !== strpos( $readme, 'Inner ring' ) && false !== strpos( $readme, 'Outer ring' ), 'README ring documentation is missing.' );
ko_accessiyes_assert( false !== strpos( $wp_readme, 'Does this plugin make my site ADA compliant?' ), 'Compliance limitation FAQ is missing.' );
ko_accessiyes_assert( false !== strpos( $uninstall, "delete_option( 'ko_accessiyes_css_customizations' )" ), 'Uninstall option cleanup is missing.' );

$public_source = $main_php . $frontend . $admin_css . $readme . $provenance . $wp_readme;
ko_accessiyes_assert( false === strpos( $public_source, 'Piedmont Triad' ), 'Public plugin source contains client-specific wording.' );
ko_accessiyes_assert( 0 === preg_match( '/[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}/i', $public_source ), 'Public plugin source contains a site-specific UUID.' );

if ( ! empty( $errors ) ) {
	fwrite( STDERR, "FAILED\n- " . implode( "\n- ", $errors ) . "\n" );
	exit( 1 );
}

echo "All static behavior checks passed.\n";
