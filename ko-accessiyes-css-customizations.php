<?php
/**
 * Plugin Name: KO: AccessiYES CSS Customizations
 * Plugin URI: https://velaagency.com
 * Description: Loads a site-specific AccessiYES script, mirrors the vendor widget controls, and adds responsive positioning and ring styling inside the widget's Shadow DOM.
 * Version: 1.1.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: KO
 * Author URI: mailto:ko@velaagency.com
 * Text Domain: ko-accessiyes-css-customizations
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'KO_ACCESSIYES_CSS_VERSION', '1.1.0' );
define( 'KO_ACCESSIYES_CSS_FILE', __FILE__ );
define( 'KO_ACCESSIYES_CSS_URL', plugin_dir_url( __FILE__ ) );

final class KO_AccessiYES_CSS_Customizations {
	const OPTION_KEY       = 'ko_accessiyes_css_customizations';
	const SETTINGS_GROUP   = 'ko_accessiyes_css_customizations_settings';
	const MENU_SLUG        = 'ko-accessiyes-css-customizations';
	const SCRIPT_HANDLE    = 'ko-accessiyes-css-customizations';
	const STYLE_ID         = 'ko-accessiyes-css-customizations-rules';
	const GENERATOR_URL    = 'https://www.accessiyes.com/get-your-script/';
	const FRONTEND_TIMEOUT = 20000;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_script' ), 100 );
		add_filter( 'plugin_action_links_' . plugin_basename( KO_ACCESSIYES_CSS_FILE ), array( $this, 'add_plugin_action_links' ) );
	}

	public static function get_default_settings() {
		return array(
			'script_snippet' => '',
			'widget_enabled' => true,
			'status'         => array(
				'desktop' => true,
				'tablet'  => true,
				'mobile'  => true,
			),
			'appearance'     => array(
				'icon_id'         => 'default',
				'icon_size'       => 48,
				'primary_color'   => '#1863DC',
				'label'           => 'Accessibility widget',
				'heading'         => 'Accessibility menu',
				'inner_icon_size' => 0,
			),
			'language'       => array(
				'default'  => 'en',
				'selected' => array(),
			),
			'keyboard'       => array(
				'enabled'  => false,
				'shortcut' => 'alt+a',
			),
			'statement'      => array(
				'enabled'           => false,
				'url'               => '',
				'display_in_widget' => false,
				'generated_date'    => '',
				'form_data'         => array(
					'company_name'       => '',
					'business_email'     => '',
					'website'            => '',
					'wcag_standard'      => 'WCAG 2.2 Level AA',
					'conformance_status' => 'fully-conformant',
				),
			),
			'breakpoints'    => array(
				'mobile_max' => 767,
				'tablet_max' => 1024,
			),
			'position'       => array(
				'desktop' => array( 'corner' => 'bottom-right', 'horizontal' => 20, 'vertical' => 20 ),
				'tablet'  => array( 'corner' => 'bottom-right', 'horizontal' => 20, 'vertical' => 20 ),
				'mobile'  => array( 'corner' => 'bottom-right', 'horizontal' => 20, 'vertical' => 20 ),
			),
			'rings'          => array(
				'inner_enabled' => true,
				'inner_width'   => 3,
				'inner_color'   => '#FFFFFF',
				'outer_enabled' => true,
				'outer_width'   => 4,
				'outer_color'   => '',
			),
			'modules'        => array(
				'profiles'   => array( 'enabled' => true ),
				'content'    => array(
					'adjustFontSizing' => array( 'enabled' => true ),
					'highlightTitle'   => array( 'enabled' => true ),
					'highlightLinks'   => array( 'enabled' => true ),
					'dyslexicFont'     => array( 'enabled' => true ),
					'fontWeight'       => array( 'enabled' => true ),
					'letterSpacing'    => array( 'enabled' => true ),
					'lineHeight'       => array( 'enabled' => true ),
					'textAlign'        => array( 'enabled' => true ),
				),
				'color'      => array(
					'darkContrast'   => array( 'enabled' => true ),
					'lightContrast'  => array( 'enabled' => true ),
					'highContrast'   => array( 'enabled' => true ),
					'highSaturation' => array( 'enabled' => true ),
					'lowSaturation'  => array( 'enabled' => true ),
					'monochrome'     => array( 'enabled' => true ),
				),
				'navigation' => array(
					'readingGuide'    => array( 'enabled' => true ),
					'pauseAnimations' => array( 'enabled' => true ),
					'bigCursor'       => array( 'enabled' => true ),
				),
				'pageRead'   => array( 'enabled' => true ),
				'muteSounds' => array( 'enabled' => true ),
			),
			'advanced_css'    => '',
		);
	}

	public static function activate() {
		$current = get_option( self::OPTION_KEY, false );

		if ( false === $current ) {
			add_option( self::OPTION_KEY, self::get_default_settings(), '', false );
			return;
		}

		if ( is_string( $current ) ) {
			$migrated                 = self::get_default_settings();
			$migrated['advanced_css'] = trim( $current );
			update_option( self::OPTION_KEY, $migrated, false );
		}
	}

	public function add_settings_page() {
		add_options_page(
			__( 'KO: AccessiYES CSS Customizations', 'ko-accessiyes-css-customizations' ),
			__( 'AccessiYES', 'ko-accessiyes-css-customizations' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => self::get_default_settings(),
				'show_in_rest'      => false,
			)
		);
	}

	public function get_settings() {
		$stored   = get_option( self::OPTION_KEY, self::get_default_settings() );
		$defaults = self::get_default_settings();

		if ( is_string( $stored ) ) {
			$defaults['advanced_css'] = trim( $stored );
			return $defaults;
		}

		return is_array( $stored ) ? array_replace_recursive( $defaults, $stored ) : $defaults;
	}

	public function sanitize_settings( $input ) {
		$previous = $this->get_settings();

		if ( ! is_array( $input ) ) {
			return $previous;
		}
		foreach ( array( 'status', 'appearance', 'language', 'keyboard', 'statement', 'breakpoints', 'position', 'rings', 'modules' ) as $group ) {
			if ( ! isset( $input[ $group ] ) || ! is_array( $input[ $group ] ) ) {
				$input[ $group ] = array();
			}
		}
		if ( ! isset( $input['statement']['form_data'] ) || ! is_array( $input['statement']['form_data'] ) ) {
			$input['statement']['form_data'] = array();
		}

		$settings = self::get_default_settings();
		$snippet  = isset( $input['script_snippet'] ) && is_string( $input['script_snippet'] ) ? $input['script_snippet'] : '';
		$snippet  = function_exists( 'wp_unslash' ) ? wp_unslash( $snippet ) : $snippet;
		$snippet  = trim( str_replace( "\0", '', wp_check_invalid_utf8( $snippet ) ) );

		if ( strlen( $snippet ) > 50000 ) {
			$snippet = substr( $snippet, 0, 50000 );
			add_settings_error( self::OPTION_KEY, 'ko_accessiyes_script_too_long', __( 'The script field was limited to 50,000 characters.', 'ko-accessiyes-css-customizations' ), 'warning' );
		}

		$settings['script_snippet'] = $snippet;
		$script_url                 = self::extract_script_url( $snippet );

		if ( '' !== $snippet && '' === $script_url ) {
			add_settings_error( self::OPTION_KEY, 'ko_accessiyes_invalid_script', __( 'No supported AccessiYES CDN URL was found. Paste the complete script generated by accessiyes.com.', 'ko-accessiyes-css-customizations' ), 'error' );
		}

		$settings['widget_enabled'] = ! empty( $input['widget_enabled'] );
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			$settings['status'][ $device ] = ! empty( $input['status'][ $device ] );
		}

		$icon_ids = array( 'default', 'accessibility', 'wheel', 'assist' );
		$icon_id  = isset( $input['appearance']['icon_id'] ) ? sanitize_key( $input['appearance']['icon_id'] ) : 'default';
		$settings['appearance']['icon_id'] = in_array( $icon_id, $icon_ids, true ) ? $icon_id : 'default';

		$icon_size = isset( $input['appearance']['icon_size'] ) ? (int) $input['appearance']['icon_size'] : 48;
		$settings['appearance']['icon_size'] = in_array( $icon_size, array( 32, 48, 64 ), true ) ? $icon_size : 48;
		$settings['appearance']['primary_color'] = $this->sanitize_color( $input['appearance']['primary_color'] ?? '', '#1863DC' );
		$settings['appearance']['label'] = sanitize_text_field( $input['appearance']['label'] ?? 'Accessibility widget' );
		$settings['appearance']['heading'] = sanitize_text_field( $input['appearance']['heading'] ?? 'Accessibility menu' );
		$settings['appearance']['inner_icon_size'] = $this->clamp_int( $input['appearance']['inner_icon_size'] ?? 0, 0, 64 );

		$languages = array_keys( self::get_languages() );
		$default_language = sanitize_text_field( $input['language']['default'] ?? 'en' );
		$settings['language']['default'] = in_array( $default_language, $languages, true ) ? $default_language : 'en';
		$selected_languages = isset( $input['language']['selected'] ) && is_array( $input['language']['selected'] ) ? array_map( 'sanitize_text_field', $input['language']['selected'] ) : array();
		$settings['language']['selected'] = array_values( array_unique( array_intersect( $selected_languages, $languages ) ) );

		$settings['keyboard']['enabled']  = ! empty( $input['keyboard']['enabled'] );
		$settings['keyboard']['shortcut'] = 'alt+a';
		$settings['statement']['enabled']           = ! empty( $input['statement']['enabled'] );
		$settings['statement']['url']               = esc_url_raw( $input['statement']['url'] ?? '' );
		$settings['statement']['display_in_widget'] = ! empty( $input['statement']['display_in_widget'] );
		$settings['statement']['form_data']['company_name']   = sanitize_text_field( $input['statement']['form_data']['company_name'] ?? '' );
		$settings['statement']['form_data']['business_email'] = sanitize_email( $input['statement']['form_data']['business_email'] ?? '' );
		$settings['statement']['form_data']['website']        = esc_url_raw( $input['statement']['form_data']['website'] ?? '' );

		$wcag_standard = sanitize_text_field( $input['statement']['form_data']['wcag_standard'] ?? 'WCAG 2.2 Level AA' );
		$settings['statement']['form_data']['wcag_standard'] = '' !== $wcag_standard ? $wcag_standard : 'WCAG 2.2 Level AA';
		$conformance_status = sanitize_key( $input['statement']['form_data']['conformance_status'] ?? 'fully-conformant' );
		$conformance_options = array( 'fully-conformant', 'partially-conformant', 'non-conformant', 'not-assessed' );
		$settings['statement']['form_data']['conformance_status'] = in_array( $conformance_status, $conformance_options, true ) ? $conformance_status : 'fully-conformant';

		$generated_date = sanitize_text_field( $previous['statement']['generated_date'] ?? '' );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $generated_date ) ) {
			$generated_date = '';
		}
		$statement_changed = $settings['statement']['form_data'] !== ( $previous['statement']['form_data'] ?? array() );
		if ( $settings['statement']['display_in_widget'] && '' !== $settings['statement']['form_data']['company_name'] && ( '' === $generated_date || $statement_changed ) ) {
			$generated_date = gmdate( 'Y-m-d' );
		}
		$settings['statement']['generated_date'] = $generated_date;

		$mobile_max = $this->clamp_int( $input['breakpoints']['mobile_max'] ?? 767, 320, 1023 );
		$tablet_max = $this->clamp_int( $input['breakpoints']['tablet_max'] ?? 1024, $mobile_max + 1, 1920 );
		$settings['breakpoints'] = array( 'mobile_max' => $mobile_max, 'tablet_max' => $tablet_max );

		$corners = array( 'top-left', 'top-right', 'bottom-left', 'bottom-right' );
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			$corner = sanitize_key( $input['position'][ $device ]['corner'] ?? 'bottom-right' );
			$settings['position'][ $device ]['corner'] = in_array( $corner, $corners, true ) ? $corner : 'bottom-right';
			$settings['position'][ $device ]['horizontal'] = $this->clamp_int( $input['position'][ $device ]['horizontal'] ?? 20, 0, 80 );
			$settings['position'][ $device ]['vertical']   = $this->clamp_int( $input['position'][ $device ]['vertical'] ?? 20, 0, 80 );
		}

		$settings['rings']['inner_enabled'] = ! empty( $input['rings']['inner_enabled'] );
		$settings['rings']['inner_width']   = $this->clamp_int( $input['rings']['inner_width'] ?? 3, 0, 12 );
		$settings['rings']['inner_color']   = $this->sanitize_color( $input['rings']['inner_color'] ?? '', '#FFFFFF' );
		$settings['rings']['outer_enabled'] = ! empty( $input['rings']['outer_enabled'] );
		$settings['rings']['outer_width']   = $this->clamp_int( $input['rings']['outer_width'] ?? 4, 0, 20 );
		$outer_color = trim( sanitize_text_field( $input['rings']['outer_color'] ?? '' ) );
		$settings['rings']['outer_color'] = '' === $outer_color ? '' : $this->sanitize_color( $outer_color, '' );

		$module_defaults = self::get_default_settings()['modules'];
		foreach ( $module_defaults as $group => $items ) {
			if ( isset( $items['enabled'] ) ) {
				$settings['modules'][ $group ]['enabled'] = ! empty( $input['modules'][ $group ]['enabled'] );
				continue;
			}
			foreach ( $items as $module_key => $unused ) {
				unset( $unused );
				$settings['modules'][ $group ][ $module_key ]['enabled'] = ! empty( $input['modules'][ $group ][ $module_key ]['enabled'] );
			}
		}

		$settings['advanced_css'] = $this->sanitize_css( $input['advanced_css'] ?? '' );

		$should_import = ! empty( $input['import_script_settings'] ) && $snippet !== $previous['script_snippet'] && '' !== $script_url;
		if ( $should_import ) {
			$script_config = self::extract_script_config( $snippet );
			if ( $script_config ) {
				$settings = $this->import_script_config( $settings, $script_config );
				add_settings_error( self::OPTION_KEY, 'ko_accessiyes_script_imported', __( 'Supported configuration values were imported from the new AccessiYES script. Review the settings below.', 'ko-accessiyes-css-customizations' ), 'updated' );
			}
		}

		if ( $settings['statement']['display_in_widget'] ) {
			$required_statement_fields = array( 'company_name', 'business_email', 'website' );
			$missing_statement_fields  = array_filter(
				$required_statement_fields,
				function ( $field ) use ( $settings ) {
					return '' === $settings['statement']['form_data'][ $field ];
				}
			);
			if ( $missing_statement_fields ) {
				$settings['statement']['display_in_widget'] = false;
				add_settings_error( self::OPTION_KEY, 'ko_accessiyes_statement_incomplete', __( 'The generated statement was not enabled because company name, business email, and website URL are required.', 'ko-accessiyes-css-customizations' ), 'warning' );
			} elseif ( '' === $settings['statement']['generated_date'] ) {
				$settings['statement']['generated_date'] = gmdate( 'Y-m-d' );
			}
		}

		return $settings;
	}

	public function sanitize_css( $css ) {
		if ( ! is_string( $css ) ) {
			return '';
		}

		$css = function_exists( 'wp_unslash' ) ? wp_unslash( $css ) : $css;
		$css = str_replace( "\0", '', wp_check_invalid_utf8( $css ) );
		if ( preg_match( '#<\s*/?\s*style\b#i', $css ) ) {
			add_settings_error( self::OPTION_KEY, 'ko_accessiyes_css_style_tag_removed', __( 'Style tags were removed from Advanced CSS. Enter CSS rules only.', 'ko-accessiyes-css-customizations' ), 'warning' );
			$css = preg_replace( '#<\s*/?\s*style\b[^>]*>#i', '', $css );
		}
		return trim( $css );
	}

	public static function extract_script_url( $snippet ) {
		if ( ! is_string( $snippet ) || '' === trim( $snippet ) ) {
			return '';
		}

		$snippet = html_entity_decode( $snippet, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		if ( ! preg_match( '#https://cdn-cookieyes\.com/widgets/accessibility\.js\?id=([A-Za-z0-9_-]{8,})#i', $snippet, $matches ) ) {
			return '';
		}
		return 'https://cdn-cookieyes.com/widgets/accessibility.js?id=' . rawurlencode( $matches[1] );
	}

	public static function extract_script_config( $snippet ) {
		if ( ! is_string( $snippet ) || '' === trim( $snippet ) ) {
			return array();
		}

		$snippet = html_entity_decode( $snippet, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		if ( ! preg_match( '/_cyA11yConfig\s*=\s*(\{.*?\})\s*;\s*var\s+js\b/s', $snippet, $matches ) ) {
			return array();
		}
		$config = json_decode( $matches[1], true );
		return is_array( $config ) ? $config : array();
	}

	private function sanitize_color( $color, $fallback ) {
		$color = is_string( $color ) ? trim( $color ) : '';
		return preg_match( '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color ) ? strtoupper( $color ) : $fallback;
	}

	private function clamp_int( $value, $minimum, $maximum ) {
		return min( max( (int) $value, (int) $minimum ), (int) $maximum );
	}

	private function import_script_config( $settings, $config ) {
		$icon_map = array(
			'default'       => 'default',
			'accessibility' => 'accessibility',
			'wheel'         => 'wheel',
			'assist'        => 'assist',
			'icon-2'        => 'accessibility',
			'icon-3'        => 'wheel',
			'icon-4'        => 'assist',
		);
		$incoming_icon = isset( $config['iconId'] ) ? sanitize_key( $config['iconId'] ) : '';
		if ( isset( $icon_map[ $incoming_icon ] ) ) {
			$settings['appearance']['icon_id'] = $icon_map[ $incoming_icon ];
		}

		$incoming_size = isset( $config['iconSize'] ) ? (int) $config['iconSize'] : ( isset( $config['size'] ) ? (int) $config['size'] : 0 );
		if ( in_array( $incoming_size, array( 32, 48, 64 ), true ) ) {
			$settings['appearance']['icon_size'] = $incoming_size;
		}

		$incoming_color = $config['primaryColor'] ?? ( $config['color'] ?? '' );
		if ( is_string( $incoming_color ) && '' !== $incoming_color ) {
			$settings['appearance']['primary_color'] = $this->sanitize_color( $incoming_color, $settings['appearance']['primary_color'] );
		}
		if ( isset( $config['label'] ) || isset( $config['buttonLabel'] ) ) {
			$settings['appearance']['label'] = sanitize_text_field( $config['label'] ?? $config['buttonLabel'] );
		}
		if ( isset( $config['heading'] ) ) {
			$settings['appearance']['heading'] = sanitize_text_field( $config['heading'] );
		}

		foreach ( array( 'desktop', 'mobile' ) as $device ) {
			if ( isset( $config['status'][ $device ] ) ) {
				$settings['status'][ $device ] = (bool) $config['status'][ $device ];
			}
			if ( isset( $config['position'][ $device ] ) ) {
				$corner = sanitize_key( $config['position'][ $device ] );
				if ( in_array( $corner, array( 'top-left', 'top-right', 'bottom-left', 'bottom-right' ), true ) ) {
					$settings['position'][ $device ]['corner'] = $corner;
				}
			}

			if ( isset( $config['offset'][ $device ] ) && is_array( $config['offset'][ $device ] ) ) {
				$settings['position'][ $device ]['horizontal'] = $this->clamp_int( $config['offset'][ $device ]['horizontal'] ?? 20, 0, 80 );
				$settings['position'][ $device ]['vertical']   = $this->clamp_int( $config['offset'][ $device ]['vertical'] ?? 20, 0, 80 );
			} elseif ( isset( $config['margins'][ $device ] ) && is_array( $config['margins'][ $device ] ) ) {
				$corner         = $settings['position'][ $device ]['corner'];
				$horizontal_key = false !== strpos( $corner, 'left' ) ? 'left' : 'right';
				$vertical_key   = false !== strpos( $corner, 'top' ) ? 'top' : 'bottom';
				$settings['position'][ $device ]['horizontal'] = $this->clamp_int( $config['margins'][ $device ][ $horizontal_key ] ?? 20, 0, 80 );
				$settings['position'][ $device ]['vertical']   = $this->clamp_int( $config['margins'][ $device ][ $vertical_key ] ?? 20, 0, 80 );
			}
		}

		$settings['status']['tablet']                 = $settings['status']['desktop'];
		$settings['position']['tablet']['corner']     = $settings['position']['desktop']['corner'];
		$settings['position']['tablet']['horizontal'] = $settings['position']['desktop']['horizontal'];
		$settings['position']['tablet']['vertical']   = $settings['position']['desktop']['vertical'];

		$languages = array_keys( self::get_languages() );
		if ( isset( $config['language']['default'] ) && in_array( $config['language']['default'], $languages, true ) ) {
			$settings['language']['default'] = $config['language']['default'];
		}
		if ( isset( $config['language']['selected'] ) && is_array( $config['language']['selected'] ) ) {
			$settings['language']['selected'] = array_values( array_unique( array_intersect( $config['language']['selected'], $languages ) ) );
		}
		if ( isset( $config['keyboard']['enabled'] ) ) {
			$settings['keyboard']['enabled'] = (bool) $config['keyboard']['enabled'];
		}
		if ( isset( $config['modules']['statement'] ) ) {
			$incoming_statement = $config['modules']['statement'];
			$settings['statement']['enabled']           = ! empty( $incoming_statement['enabled'] );
			$settings['statement']['url']               = esc_url_raw( $incoming_statement['url'] ?? '' );
			$settings['statement']['display_in_widget'] = ! empty( $incoming_statement['displayInWidget'] );
			$generated_date = sanitize_text_field( $incoming_statement['generatedDate'] ?? '' );
			$settings['statement']['generated_date'] = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $generated_date ) ? $generated_date : '';
			if ( isset( $incoming_statement['formData'] ) && is_array( $incoming_statement['formData'] ) ) {
				$form_data = $incoming_statement['formData'];
				$settings['statement']['form_data']['company_name']   = sanitize_text_field( $form_data['companyName'] ?? '' );
				$settings['statement']['form_data']['business_email'] = sanitize_email( $form_data['businessEmail'] ?? '' );
				$settings['statement']['form_data']['website']        = esc_url_raw( $form_data['website'] ?? '' );
				$settings['statement']['form_data']['wcag_standard']  = sanitize_text_field( $form_data['wcagStandard'] ?? 'WCAG 2.2 Level AA' );
				$conformance_status = sanitize_key( $form_data['conformanceStatus'] ?? 'fully-conformant' );
				$settings['statement']['form_data']['conformance_status'] = in_array( $conformance_status, array( 'fully-conformant', 'partially-conformant', 'non-conformant', 'not-assessed' ), true ) ? $conformance_status : 'fully-conformant';
			}
		}

		$module_defaults = self::get_default_settings()['modules'];
		foreach ( $module_defaults as $group => $items ) {
			if ( isset( $items['enabled'] ) ) {
				if ( isset( $config['modules'][ $group ]['enabled'] ) ) {
					$settings['modules'][ $group ]['enabled'] = (bool) $config['modules'][ $group ]['enabled'];
				}
				continue;
			}
			foreach ( $items as $module_key => $unused ) {
				unset( $unused );
				if ( isset( $config['modules'][ $group ][ $module_key ]['enabled'] ) ) {
					$settings['modules'][ $group ][ $module_key ]['enabled'] = (bool) $config['modules'][ $group ][ $module_key ]['enabled'];
				}
			}
		}

		if ( isset( $config['features'] ) && is_array( $config['features'] ) ) {
			$feature_map = array(
				'fontSize'        => array( 'content', 'adjustFontSizing' ),
				'readableFont'    => array( 'content', 'dyslexicFont' ),
				'highlightTitles' => array( 'content', 'highlightTitle' ),
				'highlightLinks'  => array( 'content', 'highlightLinks' ),
				'letterSpacing'   => array( 'content', 'letterSpacing' ),
				'lineHeight'      => array( 'content', 'lineHeight' ),
				'fontWeight'      => array( 'content', 'fontWeight' ),
				'alignLeft'       => array( 'content', 'textAlign' ),
				'darkContrast'    => array( 'color', 'darkContrast' ),
				'lightContrast'   => array( 'color', 'lightContrast' ),
				'highContrast'    => array( 'color', 'highContrast' ),
				'highSaturation'  => array( 'color', 'highSaturation' ),
				'lowSaturation'   => array( 'color', 'lowSaturation' ),
				'monochrome'      => array( 'color', 'monochrome' ),
				'readingGuide'    => array( 'navigation', 'readingGuide' ),
				'pauseAnimations' => array( 'navigation', 'pauseAnimations' ),
				'bigCursor'       => array( 'navigation', 'bigCursor' ),
				'pageRead'        => array( 'pageRead', null ),
				'muteSounds'      => array( 'muteSounds', null ),
			);
			foreach ( $feature_map as $feature => $path ) {
				if ( ! array_key_exists( $feature, $config['features'] ) ) {
					continue;
				}
				if ( null === $path[1] ) {
					$settings['modules'][ $path[0] ]['enabled'] = (bool) $config['features'][ $feature ];
				} else {
					$settings['modules'][ $path[0] ][ $path[1] ]['enabled'] = (bool) $config['features'][ $feature ];
				}
			}
			$profile_keys   = array( 'seizureSafeProfile', 'lowVisionProfile', 'adhdProfile', 'cognitiveProfile' );
			$profile_values = array();
			foreach ( $profile_keys as $profile_key ) {
				if ( array_key_exists( $profile_key, $config['features'] ) ) {
					$profile_values[] = (bool) $config['features'][ $profile_key ];
				}
			}
			if ( $profile_values ) {
				$settings['modules']['profiles']['enabled'] = in_array( true, $profile_values, true );
			}
		}

		return $settings;
	}

	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'settings_page_' . self::MENU_SLUG !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style( 'ko-accessiyes-css-customizations-admin', KO_ACCESSIYES_CSS_URL . 'assets/css/admin.css', array(), KO_ACCESSIYES_CSS_VERSION );
	}

	public function enqueue_frontend_script() {
		$settings          = $this->get_settings();
		$script_url        = self::extract_script_url( $settings['script_snippet'] );
		$accessiyes_active = $this->is_accessiyes_active();

		if ( ! $settings['widget_enabled'] ) {
			return;
		}
		if ( ! $accessiyes_active && ( '' === $script_url || ( ! $settings['status']['desktop'] && ! $settings['status']['tablet'] && ! $settings['status']['mobile'] ) ) ) {
			return;
		}

		wp_enqueue_script( self::SCRIPT_HANDLE, KO_ACCESSIYES_CSS_URL . 'assets/js/frontend.js', array(), KO_ACCESSIYES_CSS_VERSION, true );
		$config = array(
			'loadVendor'     => ! $accessiyes_active,
			'scriptUrl'      => $accessiyes_active ? '' : $script_url,
			'hostSelector'   => '#cya11y-container',
			'buttonSelector' => '.cya11y-menu-btn',
			'styleId'        => self::STYLE_ID,
			'timeoutMs'      => self::FRONTEND_TIMEOUT,
			'breakpoints'    => $settings['breakpoints'],
			'layouts'        => array(
				'desktop' => array_merge( array( 'enabled' => (bool) $settings['status']['desktop'] ), $settings['position']['desktop'] ),
				'tablet'  => array_merge( array( 'enabled' => (bool) $settings['status']['tablet'] ), $settings['position']['tablet'] ),
				'mobile'  => array_merge( array( 'enabled' => (bool) $settings['status']['mobile'] ), $settings['position']['mobile'] ),
			),
			'widgetConfig'   => $this->build_widget_config( $settings ),
			'shadowCss'      => $this->build_shadow_css( $settings ),
		);
		if ( $settings['statement']['display_in_widget'] && '' !== $settings['statement']['form_data']['company_name'] ) {
			$config['statementTemplates'] = self::get_statement_templates();
		}
		$encoded_config = wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
		if ( false !== $encoded_config ) {
			wp_add_inline_script( self::SCRIPT_HANDLE, 'window.KOAccessiYESManager = ' . $encoded_config . ';', 'before' );
		}
	}

	public function build_widget_config( $settings ) {
		return array(
			'status'       => array( 'mobile' => true, 'desktop' => true ),
			'iconId'       => $settings['appearance']['icon_id'],
			'iconSize'     => (int) $settings['appearance']['icon_size'],
			'label'        => $settings['appearance']['label'],
			'heading'      => $settings['appearance']['heading'],
			'position'     => array(
				'mobile'  => $settings['position']['mobile']['corner'],
				'desktop' => $settings['position']['desktop']['corner'],
			),
			'language'     => array(
				'default'  => $settings['language']['default'],
				'selected' => $settings['language']['selected'],
			),
			'margins'      => array(
				'desktop' => $this->build_vendor_margins( $settings['position']['desktop'] ),
				'mobile'  => $this->build_vendor_margins( $settings['position']['mobile'] ),
			),
			'primaryColor' => $settings['appearance']['primary_color'],
			'keyboard'     => array( 'enabled' => (bool) $settings['keyboard']['enabled'], 'shortcut' => 'alt+a' ),
			'modules'      => array(
				'color'      => $settings['modules']['color'],
				'content'    => $settings['modules']['content'],
				'navigation' => $settings['modules']['navigation'],
				'statement'  => array(
					'enabled'         => (bool) $settings['statement']['enabled'],
					'url'             => $settings['statement']['url'],
					'displayInWidget' => (bool) $settings['statement']['display_in_widget'],
					'generatedDate'   => $settings['statement']['generated_date'],
					'formData'        => array(
						'companyName'       => $settings['statement']['form_data']['company_name'],
						'businessEmail'     => $settings['statement']['form_data']['business_email'],
						'website'           => $settings['statement']['form_data']['website'],
						'wcagStandard'      => $settings['statement']['form_data']['wcag_standard'],
						'conformanceStatus' => $settings['statement']['form_data']['conformance_status'],
					),
				),
				'profiles'   => $settings['modules']['profiles'],
				'pageRead'   => $settings['modules']['pageRead'],
				'muteSounds' => $settings['modules']['muteSounds'],
			),
		);
	}

	private static function get_statement_templates() {
		return array(
			'en' => array(
				'template' => '<h2>Our accessibility approach</h2><p><strong>{{company_name}}</strong> wants everyone to be able to use <strong>{{website_url}}</strong>. We consider accessibility as the site changes and welcome feedback from visitors who encounter a barrier.</p><h2>Target and current status</h2><p>Our current target is <strong>{{accessibility_standard}}</strong>. Based on the assessment selected for this statement, the site is <strong>{{conformance_status}}</strong>.</p><p>This status is a point-in-time assessment, not a promise that every page or third-party feature is free of barriers. Older content and services supplied by others may require additional work.</p><h2>Visitor tools</h2><p>The site provides an AccessiYES menu with optional display and navigation adjustments. These preferences supplement the accessibility work performed on the site itself.</p><h2>Request assistance or report a problem</h2><p>Email <a href="mailto:{{contact_email}}"><strong>{{contact_email}}</strong></a> if you need help using the site or want to report a problem. Including the affected page address, what you were trying to do, and the assistive technology or browser used will help us investigate.</p><h2>Review</h2><p>We will review reported barriers and prioritize improvements that are within our control.</p><p><em>Statement reviewed: {{last_updated}}</em></p>',
				'conformance_options' => array(
					'fully-conformant'     => 'Fully conformant',
					'partially-conformant' => 'Partially conformant',
					'non-conformant'       => 'Non conformant',
					'not-assessed'         => 'Not assessed',
				),
			),
		);
	}

	private function build_vendor_margins( $position ) {
		return array(
			'top'    => (int) $position['vertical'],
			'bottom' => (int) $position['vertical'],
			'left'   => (int) $position['horizontal'],
			'right'  => (int) $position['horizontal'],
		);
	}

	public function build_shadow_css( $settings ) {
		$inner_border = $settings['rings']['inner_enabled'] ? sprintf( '%dpx solid %s', (int) $settings['rings']['inner_width'], $settings['rings']['inner_color'] ) : '0';
		$outer_color  = '' !== $settings['rings']['outer_color'] ? $settings['rings']['outer_color'] : $settings['appearance']['primary_color'];
		$shadow_parts = array();
		if ( $settings['rings']['outer_enabled'] && (int) $settings['rings']['outer_width'] > 0 ) {
			$shadow_parts[] = sprintf( '0 0 0 %dpx %s', (int) $settings['rings']['outer_width'], $outer_color );
		}
		$shadow_parts[] = '0 5px 15px #252c6126';
		$shadow_parts[] = '0 2px 4px #5d649433';
		$box_shadow     = implode( ', ', $shadow_parts );
		$button_size    = (int) $settings['appearance']['icon_size'];
		$focus_offset   = $settings['rings']['outer_enabled'] ? (int) $settings['rings']['outer_width'] + 3 : 3;

		$css  = "/***** KO AccessiYES Managed Widget Styles *****/\n\n";
		$css .= "/*** Launcher Appearance ***/\n";
		$css .= ".cya11y-widget-icon,\n.cya11y-menu-btn {\n\twidth: {$button_size}px !important;\n\theight: {$button_size}px !important;\n}\n\n";
		$css .= ".cya11y-menu-btn {\n\tborder: {$inner_border} !important;\n\tbox-shadow: {$box_shadow} !important;\n}\n\n";
		$css .= "button.cya11y-menu-btn:focus-visible {\n\toutline-offset: {$focus_offset}px !important;\n\tbox-shadow: {$box_shadow} !important;\n}\n";

		if ( (int) $settings['appearance']['inner_icon_size'] > 0 ) {
			$inner_icon_size = (int) $settings['appearance']['inner_icon_size'];
			$css .= "\n.cya11y-menu-btn .cya11y-btn-content {\n\tdisplay: flex !important;\n\talign-items: center !important;\n\tjustify-content: center !important;\n\twidth: 100% !important;\n\theight: 100% !important;\n\tline-height: 0 !important;\n}\n\n";
			$css .= ".cya11y-menu-btn svg,\n.cya11y-menu-btn .cya11y-btn-content svg {\n\twidth: {$inner_icon_size}px !important;\n\tmin-width: {$inner_icon_size}px !important;\n\tmax-width: {$inner_icon_size}px !important;\n\theight: auto !important;\n\tmin-height: 0 !important;\n\tmax-height: none !important;\n}\n";
		}

		$mobile_max = (int) $settings['breakpoints']['mobile_max'];
		$tablet_max = (int) $settings['breakpoints']['tablet_max'];
		$css .= "\n/*** Responsive Visibility and Positioning ***/\n";
		$css .= $this->build_device_css( 'mobile', "(max-width: {$mobile_max}px)", $settings );
		$css .= $this->build_device_css( 'tablet', '(min-width: ' . ( $mobile_max + 1 ) . "px) and (max-width: {$tablet_max}px)", $settings );
		$css .= $this->build_device_css( 'desktop', '(min-width: ' . ( $tablet_max + 1 ) . 'px)', $settings );
		$css .= "\n/*** High-Contrast Preference Protection ***/\n";
		$css .= "@media (prefers-contrast: high) {\n\t.cya11y-menu-btn {\n\t\tborder: 3px solid transparent !important;\n\t\tbox-shadow: 0 0 0 4px #000000, 0 5px 15px #252c614d !important;\n\t}\n\tbutton.cya11y-menu-btn:focus-visible {\n\t\toutline: 3px solid #000000 !important;\n\t\toutline-offset: 7px !important;\n\t}\n}\n";
		$css .= "\n@media (forced-colors: active) {\n\t.cya11y-menu-btn {\n\t\tborder: 2px solid ButtonText !important;\n\t\tbox-shadow: none !important;\n\t\tforced-color-adjust: auto;\n\t}\n}\n";
		$css .= "\n/***** END KO AccessiYES Managed Widget Styles *****/";

		if ( '' !== trim( $settings['advanced_css'] ) ) {
			$css .= "\n\n/***** KO AccessiYES Advanced CSS *****/\n" . trim( $settings['advanced_css'] ) . "\n/***** END KO AccessiYES Advanced CSS *****/";
		}
		return $css;
	}

	private function build_device_css( $device, $media_query, $settings ) {
		$enabled  = (bool) $settings['status'][ $device ];
		$position = $settings['position'][ $device ];
		$css      = "\n@media {$media_query} {\n";
		if ( ! $enabled ) {
			return $css . "\t.cya11y-widget-icon,\n\t.cya11y-menu-btn,\n\t.cya11y-menu,\n\t.cya11y-overlay {\n\t\tdisplay: none !important;\n\t}\n}\n";
		}

		$horizontal_property = false !== strpos( $position['corner'], 'left' ) ? 'left' : 'right';
		$horizontal_opposite = 'left' === $horizontal_property ? 'right' : 'left';
		$vertical_property   = false !== strpos( $position['corner'], 'top' ) ? 'top' : 'bottom';
		$vertical_opposite   = 'top' === $vertical_property ? 'bottom' : 'top';
		$css .= "\t.cya11y-widget-icon { display: block !important; }\n";
		$css .= "\t.cya11y-menu-btn { display: flex !important; }\n";
		$css .= "\t.cya11y-widget-icon,\n\t.cya11y-menu-btn {\n";
		$css .= sprintf( "\t\t%s: %dpx !important;\n", $vertical_property, (int) $position['vertical'] );
		$css .= sprintf( "\t\t%s: auto !important;\n", $vertical_opposite );
		$css .= sprintf( "\t\t%s: %dpx !important;\n", $horizontal_property, (int) $position['horizontal'] );
		$css .= sprintf( "\t\t%s: auto !important;\n", $horizontal_opposite );
		$css .= "\t}\n";
		$css .= "\t.cya11y-menu {\n";
		$css .= sprintf( "\t\t%s: 0 !important;\n", $horizontal_property );
		$css .= sprintf( "\t\t%s: auto !important;\n", $horizontal_opposite );
		return $css . "\t}\n}\n";
	}

	public function add_plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( admin_url( 'options-general.php?page=' . self::MENU_SLUG ) ),
			esc_html__( 'Settings', 'ko-accessiyes-css-customizations' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function is_accessiyes_active() {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
		}
		foreach ( $active_plugins as $plugin_file ) {
			if ( 0 === strpos( $plugin_file, 'accessibility-widget/' ) ) {
				return true;
			}
		}
		return false;
	}

	private static function get_languages() {
		return array(
			'am' => 'አማርኛ (Amharic)', 'ar' => 'العربية (Arabic)', 'bg' => 'български (Bulgarian)',
			'bn' => 'বাংলা (Bengali)', 'ca' => 'Català (Catalan)', 'cs' => 'čeština (Czech)',
			'da' => 'Danish (Denmark)', 'de' => 'Deutsch (German)', 'el' => 'Ελληνικά (Greek)',
			'en' => 'English', 'es' => 'Español (Spanish)', 'fa' => 'فارسی (Persian)',
			'fi' => 'suomi (Finnish)', 'fil' => 'Tagalog (Filipino)', 'fr' => 'Français (French)',
			'he' => 'עברית (Hebrew)', 'hi' => 'हिन्दी (Hindi)', 'hr' => 'Hrvatski (Croatian)',
			'hu' => 'Magyar (Hungarian)', 'id' => 'Bahasa Indonesia', 'it' => 'Italiano (Italian)',
			'ja' => '日本語 (Japanese)', 'ka' => 'ქართული (Georgian)', 'kn' => 'ಕನ್ನಡ (Kannada)',
			'ko' => '한국어 (Korean)', 'ku' => 'Kurdî (Kurdish)', 'lv' => 'Latviešu (Latvian)',
			'lt' => 'Lietuvių (Lithuanian)', 'lb' => 'Lëtzebuergesch (Luxembourgish)', 'ml' => 'മലയാളം (Malayalam)',
			'mn' => 'Монгол (Mongolian)', 'ms' => 'Bahasa Malaysia (Malay)', 'my' => 'မြန်မာ (Burmese)',
			'nl' => 'Nederlands (Dutch)', 'no' => 'Norsk (Norwegian)', 'nb' => 'Norsk bokmål',
			'pa' => 'ਪੰਜਾਬੀ (Punjabi)', 'pl' => 'Polski (Polish)', 'pt' => 'Português (Portuguese)',
			'ro' => 'Română (Romanian)', 'ru' => 'Русский (Russian)', 'si' => 'සිංහල (Sinhala)',
			'sk' => 'slovenčina (Slovak)', 'sl' => 'slovenščina (Slovenian)', 'sr' => 'Srpski (Serbian)',
			'sv' => 'Svenska (Swedish)', 'sw' => 'Kiswahili (Swahili)', 'ta' => 'தமிழ் (Tamil)',
			'te' => 'తెలుగు (Telugu)', 'th' => 'ไทย (Thai)', 'tr' => 'Türkçe (Turkish)',
			'uk' => 'Українська (Ukrainian)', 'ur' => 'اردو (Urdu)', 'vi' => 'Tiếng Việt (Vietnamese)',
			'zh-Hans' => '简体中文 (Simplified Chinese)', 'zh-Hant' => '繁體中文 (Traditional Chinese)',
		);
	}

	private static function get_module_groups() {
		return array(
			'Accessibility profile' => array(
				array( 'profiles', null, 'Accessibility profiles' ),
			),
			'Text and Reading' => array(
				array( 'content', 'adjustFontSizing', 'Adjust font sizing' ),
				array( 'content', 'highlightTitle', 'Highlight title' ),
				array( 'content', 'highlightLinks', 'Highlight links' ),
				array( 'content', 'dyslexicFont', 'Dyslexia-friendly font' ),
				array( 'content', 'fontWeight', 'Font weight' ),
				array( 'content', 'letterSpacing', 'Letter spacing' ),
				array( 'content', 'lineHeight', 'Line height' ),
				array( 'content', 'textAlign', 'Text align' ),
			),
			'Color and Contrast' => array(
				array( 'color', 'darkContrast', 'Dark contrast' ),
				array( 'color', 'lightContrast', 'Light contrast' ),
				array( 'color', 'highContrast', 'High contrast' ),
				array( 'color', 'highSaturation', 'High saturation' ),
				array( 'color', 'lowSaturation', 'Low saturation' ),
				array( 'color', 'monochrome', 'Monochrome' ),
			),
			'Navigation and Interaction' => array(
				array( 'navigation', 'readingGuide', 'Reading guide' ),
				array( 'navigation', 'pauseAnimations', 'Pause animations' ),
				array( 'navigation', 'bigCursor', 'Big cursor' ),
				array( 'pageRead', null, 'Page read' ),
				array( 'muteSounds', null, 'Mute sounds' ),
			),
		);
	}

	private function field_name( $path ) {
		return self::OPTION_KEY . '[' . implode( '][', $path ) . ']';
	}

	private function render_checkbox( $path, $checked_value, $label, $description = '' ) {
		$id = 'ko-accessiyes-' . sanitize_html_class( implode( '-', $path ) );
		?>
		<label class="ko-accessiyes-toggle" for="<?php echo esc_attr( $id ); ?>">
			<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->field_name( $path ) ); ?>" value="1" <?php checked( $checked_value ); ?>>
			<span><strong><?php echo esc_html( $label ); ?></strong><?php if ( '' !== $description ) : ?><small><?php echo esc_html( $description ); ?></small><?php endif; ?></span>
		</label>
		<?php
	}

	private function render_position_card( $device, $label, $settings ) {
		$position = $settings['position'][ $device ];
		?>
		<fieldset class="ko-accessiyes-device-card">
			<legend><?php echo esc_html( $label ); ?></legend>
			<?php $this->render_checkbox( array( 'status', $device ), $settings['status'][ $device ], sprintf( __( 'Show on %s', 'ko-accessiyes-css-customizations' ), strtolower( $label ) ) ); ?>
			<label><span><?php esc_html_e( 'Corner', 'ko-accessiyes-css-customizations' ); ?></span>
				<select name="<?php echo esc_attr( $this->field_name( array( 'position', $device, 'corner' ) ) ); ?>">
					<?php foreach ( array( 'top-left' => 'Top left', 'top-right' => 'Top right', 'bottom-left' => 'Bottom left', 'bottom-right' => 'Bottom right' ) as $value => $corner_label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $position['corner'], $value ); ?>><?php echo esc_html( $corner_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<div class="ko-accessiyes-number-row">
				<label><span><?php esc_html_e( 'Horizontal margin', 'ko-accessiyes-css-customizations' ); ?></span><span class="ko-accessiyes-inline-number"><input type="number" min="0" max="80" step="1" name="<?php echo esc_attr( $this->field_name( array( 'position', $device, 'horizontal' ) ) ); ?>" value="<?php echo esc_attr( $position['horizontal'] ); ?>"><small>px</small></span></label>
				<label><span><?php esc_html_e( 'Vertical margin', 'ko-accessiyes-css-customizations' ); ?></span><span class="ko-accessiyes-inline-number"><input type="number" min="0" max="80" step="1" name="<?php echo esc_attr( $this->field_name( array( 'position', $device, 'vertical' ) ) ); ?>" value="<?php echo esc_attr( $position['vertical'] ); ?>"><small>px</small></span></label>
			</div>
		</fieldset>
		<?php
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings          = $this->get_settings();
		$accessiyes_active = $this->is_accessiyes_active();
		$script_ready      = '' !== self::extract_script_url( $settings['script_snippet'] );
		?>
		<div class="wrap ko-accessiyes-css-wrap">
			<header class="ko-accessiyes-css-hero">
				<a class="ko-accessiyes-css-brand" href="https://velaagency.com" target="_blank" rel="noopener"><img src="<?php echo esc_url( KO_ACCESSIYES_CSS_URL . 'assets/images/vela-logo-white.svg' ); ?>" alt="<?php esc_attr_e( 'Vela', 'ko-accessiyes-css-customizations' ); ?>"></a>
				<div><h1><?php esc_html_e( 'KO: AccessiYES CSS Customizations', 'ko-accessiyes-css-customizations' ); ?></h1><p><?php esc_html_e( 'Reusable script management, vendor-equivalent controls, and Shadow DOM customizations.', 'ko-accessiyes-css-customizations' ); ?></p></div>
			</header>

			<?php if ( $accessiyes_active ) : ?>
				<div class="notice notice-warning inline ko-accessiyes-conflict-notice"><p><strong><?php esc_html_e( 'The official AccessiYES WordPress plugin is active.', 'ko-accessiyes-css-customizations' ); ?></strong> <?php esc_html_e( 'This plugin will apply its Shadow DOM styles to the existing widget but will not load a second widget. Configure this screen, then deactivate the official plugin before testing the standalone script integration.', 'ko-accessiyes-css-customizations' ); ?></p></div>
			<?php endif; ?>

			<div class="ko-accessiyes-css-layout">
				<main class="ko-accessiyes-css-main"><form action="options.php" method="post">
					<?php settings_fields( self::SETTINGS_GROUP ); ?>

					<section class="ko-accessiyes-css-panel ko-accessiyes-script-panel">
						<h2><?php esc_html_e( 'AccessiYES Installation Script', 'ko-accessiyes-css-customizations' ); ?></h2>
						<p><?php esc_html_e( 'Paste the complete script supplied by AccessiYES. The plugin extracts only the approved CDN widget URL and supported configuration values; it does not execute arbitrary pasted JavaScript.', 'ko-accessiyes-css-customizations' ); ?></p>
						<label class="screen-reader-text" for="ko-accessiyes-script-snippet"><?php esc_html_e( 'AccessiYES installation script', 'ko-accessiyes-css-customizations' ); ?></label>
						<textarea id="ko-accessiyes-script-snippet" name="<?php echo esc_attr( $this->field_name( array( 'script_snippet' ) ) ); ?>" rows="9" spellcheck="false" wrap="off"><?php echo esc_textarea( $settings['script_snippet'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Need a site-specific script?', 'ko-accessiyes-css-customizations' ); ?> <a href="<?php echo esc_url( self::GENERATOR_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open the AccessiYES script generator', 'ko-accessiyes-css-customizations' ); ?></a>.</p>
						<label class="ko-accessiyes-import-option"><input type="checkbox" name="<?php echo esc_attr( $this->field_name( array( 'import_script_settings' ) ) ); ?>" value="1" checked> <?php esc_html_e( 'When the script changes, import its supported settings into the controls below.', 'ko-accessiyes-css-customizations' ); ?></label>
					</section>

					<section class="ko-accessiyes-css-panel">
						<h2><?php esc_html_e( 'Widget Status and Appearance', 'ko-accessiyes-css-customizations' ); ?></h2>
						<?php $this->render_checkbox( array( 'widget_enabled' ), $settings['widget_enabled'], __( 'Enable the managed AccessiYES widget', 'ko-accessiyes-css-customizations' ), __( 'A valid script is also required.', 'ko-accessiyes-css-customizations' ) ); ?>
						<div class="ko-accessiyes-form-grid">
							<label><span><?php esc_html_e( 'Widget colour', 'ko-accessiyes-css-customizations' ); ?></span><input type="color" name="<?php echo esc_attr( $this->field_name( array( 'appearance', 'primary_color' ) ) ); ?>" value="<?php echo esc_attr( $settings['appearance']['primary_color'] ); ?>"></label>
							<label><span><?php esc_html_e( 'Icon shape', 'ko-accessiyes-css-customizations' ); ?></span><select name="<?php echo esc_attr( $this->field_name( array( 'appearance', 'icon_id' ) ) ); ?>"><?php foreach ( array( 'default' => 'Default', 'accessibility' => 'Accessibility person', 'wheel' => 'Wheelchair', 'assist' => 'Assist' ) as $value => $label ) : ?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['appearance']['icon_id'], $value ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label>
							<label><span><?php esc_html_e( 'Launcher size', 'ko-accessiyes-css-customizations' ); ?></span><select name="<?php echo esc_attr( $this->field_name( array( 'appearance', 'icon_size' ) ) ); ?>"><option value="32" <?php selected( $settings['appearance']['icon_size'], 32 ); ?>><?php esc_html_e( 'Small — 32px', 'ko-accessiyes-css-customizations' ); ?></option><option value="48" <?php selected( $settings['appearance']['icon_size'], 48 ); ?>><?php esc_html_e( 'Medium — 48px', 'ko-accessiyes-css-customizations' ); ?></option><option value="64" <?php selected( $settings['appearance']['icon_size'], 64 ); ?>><?php esc_html_e( 'Large — 64px', 'ko-accessiyes-css-customizations' ); ?></option></select></label>
							<label><span><?php esc_html_e( 'Inner icon width', 'ko-accessiyes-css-customizations' ); ?></span><span class="ko-accessiyes-inline-number"><input type="number" min="0" max="64" name="<?php echo esc_attr( $this->field_name( array( 'appearance', 'inner_icon_size' ) ) ); ?>" value="<?php echo esc_attr( $settings['appearance']['inner_icon_size'] ); ?>"><small>px</small></span><small><?php esc_html_e( 'Use 0 for the vendor default.', 'ko-accessiyes-css-customizations' ); ?></small></label>
							<label><span><?php esc_html_e( 'Accessible button label', 'ko-accessiyes-css-customizations' ); ?></span><input type="text" name="<?php echo esc_attr( $this->field_name( array( 'appearance', 'label' ) ) ); ?>" value="<?php echo esc_attr( $settings['appearance']['label'] ); ?>"></label>
							<label><span><?php esc_html_e( 'Widget menu heading', 'ko-accessiyes-css-customizations' ); ?></span><input type="text" name="<?php echo esc_attr( $this->field_name( array( 'appearance', 'heading' ) ) ); ?>" value="<?php echo esc_attr( $settings['appearance']['heading'] ); ?>"></label>
						</div>
					</section>

					<section class="ko-accessiyes-css-panel">
						<h2><?php esc_html_e( 'Language and Keyboard', 'ko-accessiyes-css-customizations' ); ?></h2>
						<div class="ko-accessiyes-form-grid">
							<label><span><?php esc_html_e( 'Fallback language', 'ko-accessiyes-css-customizations' ); ?></span><select name="<?php echo esc_attr( $this->field_name( array( 'language', 'default' ) ) ); ?>"><?php foreach ( self::get_languages() as $code => $language_label ) : ?><option value="<?php echo esc_attr( $code ); ?>" <?php selected( $settings['language']['default'], $code ); ?>><?php echo esc_html( $language_label ); ?></option><?php endforeach; ?></select></label>
							<label><span><?php esc_html_e( 'Visitor language switcher', 'ko-accessiyes-css-customizations' ); ?></span><select multiple size="7" name="<?php echo esc_attr( $this->field_name( array( 'language', 'selected' ) ) ); ?>[]"><?php foreach ( self::get_languages() as $code => $language_label ) : ?><option value="<?php echo esc_attr( $code ); ?>" <?php selected( in_array( $code, $settings['language']['selected'], true ) ); ?>><?php echo esc_html( $language_label ); ?></option><?php endforeach; ?></select><small><?php esc_html_e( 'Leave all unselected to hide the switcher.', 'ko-accessiyes-css-customizations' ); ?></small></label>
						</div>
						<?php $this->render_checkbox( array( 'keyboard', 'enabled' ), $settings['keyboard']['enabled'], __( 'Enable keyboard shortcut', 'ko-accessiyes-css-customizations' ), __( 'Alt+A on Windows/Linux or Option+A on macOS.', 'ko-accessiyes-css-customizations' ) ); ?>
					</section>

					<section class="ko-accessiyes-css-panel">
						<h2><?php esc_html_e( 'Responsive Positioning', 'ko-accessiyes-css-customizations' ); ?></h2>
						<p><?php esc_html_e( 'This adds a true tablet range and independently controls visibility, corner, and margins at all three breakpoints.', 'ko-accessiyes-css-customizations' ); ?></p>
						<div class="ko-accessiyes-breakpoints"><label><span><?php esc_html_e( 'Mobile maximum width', 'ko-accessiyes-css-customizations' ); ?></span><span class="ko-accessiyes-inline-number"><input type="number" min="320" max="1023" name="<?php echo esc_attr( $this->field_name( array( 'breakpoints', 'mobile_max' ) ) ); ?>" value="<?php echo esc_attr( $settings['breakpoints']['mobile_max'] ); ?>"><small>px</small></span></label><label><span><?php esc_html_e( 'Tablet maximum width', 'ko-accessiyes-css-customizations' ); ?></span><span class="ko-accessiyes-inline-number"><input type="number" min="321" max="1920" name="<?php echo esc_attr( $this->field_name( array( 'breakpoints', 'tablet_max' ) ) ); ?>" value="<?php echo esc_attr( $settings['breakpoints']['tablet_max'] ); ?>"><small>px</small></span></label></div>
						<div class="ko-accessiyes-device-grid"><?php $this->render_position_card( 'desktop', __( 'Desktop', 'ko-accessiyes-css-customizations' ), $settings ); ?><?php $this->render_position_card( 'tablet', __( 'Tablet', 'ko-accessiyes-css-customizations' ), $settings ); ?><?php $this->render_position_card( 'mobile', __( 'Mobile', 'ko-accessiyes-css-customizations' ), $settings ); ?></div>
					</section>

					<section class="ko-accessiyes-css-panel">
						<h2><?php esc_html_e( 'Launcher Rings', 'ko-accessiyes-css-customizations' ); ?></h2>
						<p><?php esc_html_e( 'The inner ring is the border; the outer ring is the solid first box-shadow layer. Keyboard focus outlines remain intact.', 'ko-accessiyes-css-customizations' ); ?></p>
						<div class="ko-accessiyes-ring-grid">
							<fieldset class="ko-accessiyes-device-card"><legend><?php esc_html_e( 'Inner ring', 'ko-accessiyes-css-customizations' ); ?></legend><?php $this->render_checkbox( array( 'rings', 'inner_enabled' ), $settings['rings']['inner_enabled'], __( 'Show inner ring', 'ko-accessiyes-css-customizations' ) ); ?><label><span><?php esc_html_e( 'Width', 'ko-accessiyes-css-customizations' ); ?></span><span class="ko-accessiyes-inline-number"><input type="number" min="0" max="12" name="<?php echo esc_attr( $this->field_name( array( 'rings', 'inner_width' ) ) ); ?>" value="<?php echo esc_attr( $settings['rings']['inner_width'] ); ?>"><small>px</small></span></label><label><span><?php esc_html_e( 'Colour', 'ko-accessiyes-css-customizations' ); ?></span><input type="color" name="<?php echo esc_attr( $this->field_name( array( 'rings', 'inner_color' ) ) ); ?>" value="<?php echo esc_attr( $settings['rings']['inner_color'] ); ?>"></label></fieldset>
							<fieldset class="ko-accessiyes-device-card"><legend><?php esc_html_e( 'Outer ring', 'ko-accessiyes-css-customizations' ); ?></legend><?php $this->render_checkbox( array( 'rings', 'outer_enabled' ), $settings['rings']['outer_enabled'], __( 'Show outer ring', 'ko-accessiyes-css-customizations' ) ); ?><label><span><?php esc_html_e( 'Width', 'ko-accessiyes-css-customizations' ); ?></span><span class="ko-accessiyes-inline-number"><input type="number" min="0" max="20" name="<?php echo esc_attr( $this->field_name( array( 'rings', 'outer_width' ) ) ); ?>" value="<?php echo esc_attr( $settings['rings']['outer_width'] ); ?>"><small>px</small></span></label><label><span><?php esc_html_e( 'Custom colour', 'ko-accessiyes-css-customizations' ); ?></span><input type="text" placeholder="<?php esc_attr_e( 'Blank matches widget colour', 'ko-accessiyes-css-customizations' ); ?>" name="<?php echo esc_attr( $this->field_name( array( 'rings', 'outer_color' ) ) ); ?>" value="<?php echo esc_attr( $settings['rings']['outer_color'] ); ?>"></label></fieldset>
						</div>
					</section>

					<section class="ko-accessiyes-css-panel">
						<h2><?php esc_html_e( 'Accessibility Statement', 'ko-accessiyes-css-customizations' ); ?></h2>
						<p><?php esc_html_e( 'Link an existing statement, or populate an independently written KO statement from the details below. A generated statement takes precedence when both options are enabled.', 'ko-accessiyes-css-customizations' ); ?></p>
						<div class="ko-accessiyes-statement-options">
							<fieldset class="ko-accessiyes-device-card">
								<legend><?php esc_html_e( 'Link an existing statement', 'ko-accessiyes-css-customizations' ); ?></legend>
								<?php $this->render_checkbox( array( 'statement', 'enabled' ), $settings['statement']['enabled'], __( 'Show an external statement link', 'ko-accessiyes-css-customizations' ) ); ?>
								<label class="ko-accessiyes-full-field"><span><?php esc_html_e( 'Accessibility statement URL', 'ko-accessiyes-css-customizations' ); ?></span><input type="url" class="large-text" name="<?php echo esc_attr( $this->field_name( array( 'statement', 'url' ) ) ); ?>" value="<?php echo esc_attr( $settings['statement']['url'] ); ?>" placeholder="https://example.com/accessibility-statement/"></label>
							</fieldset>
							<fieldset class="ko-accessiyes-device-card ko-accessiyes-generated-statement">
								<legend><?php esc_html_e( 'Generate a statement', 'ko-accessiyes-css-customizations' ); ?></legend>
								<?php $this->render_checkbox( array( 'statement', 'display_in_widget' ), $settings['statement']['display_in_widget'], __( 'Show the generated statement inside the widget', 'ko-accessiyes-css-customizations' ), __( 'Uses the information below and an English statement template.', 'ko-accessiyes-css-customizations' ) ); ?>
								<div class="ko-accessiyes-form-grid">
									<label><span><?php esc_html_e( 'Company name', 'ko-accessiyes-css-customizations' ); ?></span><input type="text" name="<?php echo esc_attr( $this->field_name( array( 'statement', 'form_data', 'company_name' ) ) ); ?>" value="<?php echo esc_attr( $settings['statement']['form_data']['company_name'] ); ?>"></label>
									<label><span><?php esc_html_e( 'Business email', 'ko-accessiyes-css-customizations' ); ?></span><input type="email" name="<?php echo esc_attr( $this->field_name( array( 'statement', 'form_data', 'business_email' ) ) ); ?>" value="<?php echo esc_attr( $settings['statement']['form_data']['business_email'] ); ?>"></label>
									<label><span><?php esc_html_e( 'Website URL', 'ko-accessiyes-css-customizations' ); ?></span><input type="url" name="<?php echo esc_attr( $this->field_name( array( 'statement', 'form_data', 'website' ) ) ); ?>" value="<?php echo esc_attr( $settings['statement']['form_data']['website'] ); ?>" placeholder="https://example.com"></label>
									<label><span><?php esc_html_e( 'Target standard', 'ko-accessiyes-css-customizations' ); ?></span><input type="text" list="ko-accessiyes-wcag-options" name="<?php echo esc_attr( $this->field_name( array( 'statement', 'form_data', 'wcag_standard' ) ) ); ?>" value="<?php echo esc_attr( $settings['statement']['form_data']['wcag_standard'] ); ?>"><datalist id="ko-accessiyes-wcag-options"><option value="WCAG 2.2 Level AA"><option value="WCAG 2.1 Level AA"><option value="WCAG 2.0 Level AA"></datalist></label>
									<label><span><?php esc_html_e( 'Conformance status', 'ko-accessiyes-css-customizations' ); ?></span><select name="<?php echo esc_attr( $this->field_name( array( 'statement', 'form_data', 'conformance_status' ) ) ); ?>"><?php foreach ( array( 'fully-conformant' => 'Fully conformant', 'partially-conformant' => 'Partially conformant', 'non-conformant' => 'Non conformant', 'not-assessed' => 'Not assessed' ) as $value => $label ) : ?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['statement']['form_data']['conformance_status'], $value ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label>
								</div>
								<p class="description"><?php esc_html_e( 'The generated wording is based entirely on your entries and assessment. Review it before publishing; it is not legal advice.', 'ko-accessiyes-css-customizations' ); ?><?php if ( '' !== $settings['statement']['generated_date'] ) : ?> <?php printf( esc_html__( 'Last generated: %s.', 'ko-accessiyes-css-customizations' ), esc_html( $settings['statement']['generated_date'] ) ); ?><?php endif; ?></p>
							</fieldset>
						</div>
					</section>

					<section class="ko-accessiyes-css-panel"><h2><?php esc_html_e( 'Widget Modules', 'ko-accessiyes-css-customizations' ); ?></h2><p><?php esc_html_e( 'These KO controls map to the module switches supported by the AccessiYES CDN widget.', 'ko-accessiyes-css-customizations' ); ?></p><div class="ko-accessiyes-module-groups">
						<?php foreach ( self::get_module_groups() as $group_label => $items ) : ?><fieldset><legend><?php echo esc_html( $group_label ); ?></legend><div class="ko-accessiyes-toggle-grid"><?php foreach ( $items as $item ) : ?><?php $module_checked = null === $item[1] ? $settings['modules'][ $item[0] ]['enabled'] : $settings['modules'][ $item[0] ][ $item[1] ]['enabled']; ?><?php $module_path = null === $item[1] ? array( 'modules', $item[0], 'enabled' ) : array( 'modules', $item[0], $item[1], 'enabled' ); ?><?php $this->render_checkbox( $module_path, $module_checked, $item[2] ); ?><?php endforeach; ?></div></fieldset><?php endforeach; ?>
					</div></section>

					<section class="ko-accessiyes-css-panel ko-accessiyes-css-editor"><h2><?php esc_html_e( 'Advanced Shadow DOM CSS', 'ko-accessiyes-css-customizations' ); ?></h2><p><?php esc_html_e( 'Optional. These rules are appended after the managed controls inside the widget’s open Shadow DOM. Do not include style tags.', 'ko-accessiyes-css-customizations' ); ?></p><label class="screen-reader-text" for="ko-accessiyes-css-editor"><?php esc_html_e( 'Advanced Shadow DOM CSS', 'ko-accessiyes-css-customizations' ); ?></label><textarea id="ko-accessiyes-css-editor" name="<?php echo esc_attr( $this->field_name( array( 'advanced_css' ) ) ); ?>" rows="18" spellcheck="false" wrap="off"><?php echo esc_textarea( $settings['advanced_css'] ); ?></textarea></section>

					<?php submit_button( __( 'Save AccessiYES Settings', 'ko-accessiyes-css-customizations' ) ); ?>
				</form></main>

				<aside class="ko-accessiyes-css-sidebar">
					<section class="ko-accessiyes-css-panel"><h2><?php esc_html_e( 'Integration Status', 'ko-accessiyes-css-customizations' ); ?></h2><p class="ko-accessiyes-css-status <?php echo $script_ready ? 'is-active' : 'is-inactive'; ?>"><span aria-hidden="true"></span><?php echo esc_html( $script_ready ? __( 'Valid AccessiYES script detected', 'ko-accessiyes-css-customizations' ) : __( 'A valid AccessiYES script is required', 'ko-accessiyes-css-customizations' ) ); ?></p><p class="ko-accessiyes-css-status <?php echo $accessiyes_active ? 'is-inactive' : 'is-active'; ?>"><span aria-hidden="true"></span><?php echo esc_html( $accessiyes_active ? __( 'Official plugin conflict detected', 'ko-accessiyes-css-customizations' ) : __( 'No official plugin conflict', 'ko-accessiyes-css-customizations' ) ); ?></p><p class="ko-accessiyes-css-status <?php echo $settings['widget_enabled'] ? 'is-active' : 'is-inactive'; ?>"><span aria-hidden="true"></span><?php echo esc_html( $settings['widget_enabled'] ? __( 'Managed widget enabled', 'ko-accessiyes-css-customizations' ) : __( 'Managed widget disabled', 'ko-accessiyes-css-customizations' ) ); ?></p></section>
					<section class="ko-accessiyes-css-panel"><h2><?php esc_html_e( 'Why a Shadow DOM Bridge?', 'ko-accessiyes-css-customizations' ); ?></h2><p><?php esc_html_e( 'AccessiYES renders its launcher and interface inside an open Shadow DOM. Theme, Customizer, Divi, and page-level CSS cannot cross that browser boundary.', 'ko-accessiyes-css-customizations' ); ?></p><p><?php esc_html_e( 'This plugin waits for the widget and adds one namespaced style element inside the shadow root. Vendor files remain untouched.', 'ko-accessiyes-css-customizations' ); ?></p></section>
					<section class="ko-accessiyes-css-panel"><h2><?php esc_html_e( 'Safe Script Handling', 'ko-accessiyes-css-customizations' ); ?></h2><p><?php esc_html_e( 'The pasted snippet is retained for reuse, but the front end loads only an allowlisted cdn-cookieyes.com accessibility.js URL. Configuration is rebuilt from these controls.', 'ko-accessiyes-css-customizations' ); ?></p></section>
					<section class="ko-accessiyes-css-panel"><h2><?php esc_html_e( 'Independent Integration', 'ko-accessiyes-css-customizations' ); ?></h2><p><?php esc_html_e( 'This is an independently developed KO/Vela integration. It is not affiliated with, sponsored by, or endorsed by AccessiYES. No AccessiYES plugin code, assets, or interface are bundled.', 'ko-accessiyes-css-customizations' ); ?></p></section>
					<section class="ko-accessiyes-css-panel"><h2><?php esc_html_e( 'Accessibility Scope', 'ko-accessiyes-css-customizations' ); ?></h2><p><?php esc_html_e( 'A widget can provide visitor tools, but it does not audit, remediate, certify, or guarantee ADA or WCAG compliance.', 'ko-accessiyes-css-customizations' ); ?></p></section>
					<section class="ko-accessiyes-css-panel"><h2><?php esc_html_e( 'Support', 'ko-accessiyes-css-customizations' ); ?></h2><p><a href="mailto:ko@velaagency.com">ko@velaagency.com</a><br><a href="https://velaagency.com" target="_blank" rel="noopener">velaagency.com</a></p></section>
				</aside>
			</div>
			<footer class="ko-accessiyes-css-footer"><?php printf( esc_html__( 'KO: AccessiYES CSS Customizations v%s', 'ko-accessiyes-css-customizations' ), esc_html( KO_ACCESSIYES_CSS_VERSION ) ); ?></footer>
		</div>
		<?php
	}
}

register_activation_hook( KO_ACCESSIYES_CSS_FILE, array( 'KO_AccessiYES_CSS_Customizations', 'activate' ) );
new KO_AccessiYES_CSS_Customizations();
