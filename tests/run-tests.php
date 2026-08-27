<?php
/**
 * Static behavior checks for KO: AccessiYES CSS Customizations.
 */

$root   = dirname( __DIR__ );
$errors = array();

function ko_accessiyes_assert( $condition, $message ) {
	global $errors;

	if ( ! $condition ) {
		$errors[] = $message;
	}
}

$main_php  = file_get_contents( $root . '/ko-accessiyes-css-customizations.php' );
$frontend  = file_get_contents( $root . '/assets/js/frontend.js' );
$admin_css = file_get_contents( $root . '/assets/css/admin.css' );
$readme    = file_get_contents( $root . '/README.md' );
$wp_readme = file_get_contents( $root . '/readme.txt' );
$uninstall = file_get_contents( $root . '/uninstall.php' );

ko_accessiyes_assert( false !== strpos( $main_php, 'Plugin Name: KO: AccessiYES CSS Customizations' ), 'Exact plugin name is missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, "Version: 1.0.0" ), 'Plugin header version is incorrect.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'Requires Plugins: accessibility-widget' ), 'AccessiYES dependency header is missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, "widget's Shadow DOM" ), 'Plugin description does not explain the Shadow DOM.' );
ko_accessiyes_assert( false !== strpos( $main_php, "add_option( self::OPTION_KEY, '', '', false )" ), 'New installations must start with a blank editor.' );
ko_accessiyes_assert( false !== strpos( $main_php, "'' === trim( \$css )" ), 'Blank CSS suppression is missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'wp_json_encode' ), 'Saved CSS is not JSON encoded.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'JSON_HEX_TAG' ), 'JSON script-boundary hardening is missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'wp_add_inline_script' ), 'Front-end configuration is not attached to the bridge script.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'plugin_action_links_' ), 'Plugins-screen Settings link is missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'manage_options' ), 'Settings capability check is missing.' );
ko_accessiyes_assert( false !== strpos( $main_php, 'style tags were removed' ) || false !== strpos( strtolower( $main_php ), 'style tags were removed' ), 'Style-tag sanitization notice is missing.' );

ko_accessiyes_assert( false !== strpos( $frontend, 'host.shadowRoot' ), 'Bridge does not inspect the Shadow DOM.' );
ko_accessiyes_assert( false !== strpos( $frontend, "root.querySelector(buttonSelector)" ), 'Bridge does not wait for the widget button.' );
ko_accessiyes_assert( false !== strpos( $frontend, "document.createElement('style')" ), 'Bridge does not create a style element.' );
ko_accessiyes_assert( false !== strpos( $frontend, 'style.textContent = css' ), 'Bridge does not assign CSS safely with textContent.' );
ko_accessiyes_assert( false === strpos( $frontend, 'innerHTML' ), 'Bridge must not inject CSS through innerHTML.' );
ko_accessiyes_assert( false !== strpos( $frontend, 'window.setInterval' ), 'Asynchronous widget retry is missing.' );
ko_accessiyes_assert( false !== strpos( $frontend, 'window.clearInterval' ), 'Retry cleanup is missing.' );

ko_accessiyes_assert( false !== strpos( $admin_css, '.ko-accessiyes-css-wrap .notice' ), 'Scoped WordPress notice styling is missing.' );
ko_accessiyes_assert( false !== strpos( $admin_css, 'color: #1d2327;' ), 'WordPress notice text contrast safeguard is missing.' );
ko_accessiyes_assert( false !== strpos( $readme, '## How the Shadow DOM Bridge Works' ), 'README Shadow DOM documentation is missing.' );
ko_accessiyes_assert( false !== strpos( $readme, 'box-shadow:' ), 'README ring-removal example is missing.' );
ko_accessiyes_assert( false !== strpos( $wp_readme, 'Shadow DOM style encapsulation' ), 'WordPress readme Shadow DOM FAQ is missing.' );
ko_accessiyes_assert( false !== strpos( $wp_readme, 'Does this plugin make my site ADA compliant?' ), 'Compliance limitation FAQ is missing.' );
ko_accessiyes_assert( false !== strpos( $uninstall, "delete_option( 'ko_accessiyes_css_customizations' )" ), 'Uninstall option cleanup is missing.' );

$public_source = $main_php . $frontend . $admin_css . $readme . $wp_readme;
ko_accessiyes_assert( false === strpos( $public_source, 'Piedmont Triad' ), 'Public plugin source contains client-specific wording.' );

if ( ! empty( $errors ) ) {
	fwrite( STDERR, "FAILED\n- " . implode( "\n- ", $errors ) . "\n" );
	exit( 1 );
}

echo "All static behavior checks passed.\n";
