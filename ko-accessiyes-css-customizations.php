<?php
/**
 * Plugin Name: KO: AccessiYES CSS Customizations
 * Plugin URI: https://velaagency.com
 * Description: Provides a Vela-branded CSS editor for AccessiYES and injects saved rules into the widget's Shadow DOM, which ordinary theme, Customizer, and page-builder CSS cannot reach.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: accessibility-widget
 * Author: KO
 * Author URI: mailto:ko@velaagency.com
 * Text Domain: ko-accessiyes-css-customizations
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'KO_ACCESSIYES_CSS_VERSION', '1.0.0' );
define( 'KO_ACCESSIYES_CSS_FILE', __FILE__ );
define( 'KO_ACCESSIYES_CSS_URL', plugin_dir_url( __FILE__ ) );

final class KO_AccessiYES_CSS_Customizations {
	const OPTION_KEY     = 'ko_accessiyes_css_customizations';
	const SETTINGS_GROUP = 'ko_accessiyes_css_customizations_settings';
	const MENU_SLUG      = 'ko-accessiyes-css-customizations';
	const SCRIPT_HANDLE  = 'ko-accessiyes-css-customizations';
	const STYLE_ID       = 'ko-accessiyes-css-customizations-rules';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_script' ), 100 );
		add_filter( 'plugin_action_links_' . plugin_basename( KO_ACCESSIYES_CSS_FILE ), array( $this, 'add_plugin_action_links' ) );
	}

	public static function activate() {
		if ( false === get_option( self::OPTION_KEY, false ) ) {
			add_option( self::OPTION_KEY, '', '', false );
		}
	}

	public function add_settings_page() {
		add_options_page(
			__( 'KO: AccessiYES CSS Customizations', 'ko-accessiyes-css-customizations' ),
			__( 'AccessiYES CSS', 'ko-accessiyes-css-customizations' ),
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
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_css' ),
				'default'           => '',
				'show_in_rest'      => false,
			)
		);
	}

	public function sanitize_css( $css ) {
		if ( ! is_string( $css ) ) {
			return '';
		}

		$css = wp_check_invalid_utf8( $css );
		$css = str_replace( "\0", '', $css );

		if ( preg_match( '#<\s*/?\s*style\b#i', $css ) ) {
			add_settings_error(
				self::OPTION_KEY,
				'ko_accessiyes_css_style_tag_removed',
				__( 'Style tags were removed. Enter CSS rules only, without opening or closing style tags.', 'ko-accessiyes-css-customizations' ),
				'warning'
			);

			$css = preg_replace( '#<\s*/?\s*style\b[^>]*>#i', '', $css );
			$css = preg_replace( '#<\s*/\s*style\b#i', '', $css );
		}

		return trim( $css );
	}

	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'settings_page_' . self::MENU_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'ko-accessiyes-css-customizations-admin',
			KO_ACCESSIYES_CSS_URL . 'assets/css/admin.css',
			array(),
			KO_ACCESSIYES_CSS_VERSION
		);
	}

	public function enqueue_frontend_script() {
		$css = get_option( self::OPTION_KEY, '' );

		if ( ! is_string( $css ) || '' === trim( $css ) ) {
			return;
		}

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			KO_ACCESSIYES_CSS_URL . 'assets/js/frontend.js',
			array(),
			KO_ACCESSIYES_CSS_VERSION,
			true
		);

		$config = array(
			'css'            => $css,
			'hostSelector'   => '#cya11y-container',
			'buttonSelector' => '.cya11y-menu-btn',
			'styleId'        => self::STYLE_ID,
			'timeoutMs'      => 20000,
		);

		$encoded_config = wp_json_encode(
			$config,
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
		);

		if ( false === $encoded_config ) {
			return;
		}

		wp_add_inline_script(
			self::SCRIPT_HANDLE,
			'window.KOAccessiYESCSS = ' . $encoded_config . ';',
			'before'
		);
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

	private function is_accessiyes_active() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$network_plugins = array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) );
			$active_plugins  = array_merge( $active_plugins, $network_plugins );
		}

		foreach ( $active_plugins as $plugin_file ) {
			if ( 0 === strpos( $plugin_file, 'accessibility-widget/' ) ) {
				return true;
			}
		}

		return false;
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$css                = get_option( self::OPTION_KEY, '' );
		$accessiyes_active  = $this->is_accessiyes_active();
		$saved_css_is_ready = is_string( $css ) && '' !== trim( $css );

		if ( $accessiyes_active ) {
			$integration_status = __( 'AccessiYES is active', 'ko-accessiyes-css-customizations' );
		} else {
			$integration_status = __( 'AccessiYES was not detected', 'ko-accessiyes-css-customizations' );
		}
		?>
		<div class="wrap ko-accessiyes-css-wrap">
			<header class="ko-accessiyes-css-hero">
				<a class="ko-accessiyes-css-brand" href="https://velaagency.com" target="_blank" rel="noopener">
					<img src="<?php echo esc_url( KO_ACCESSIYES_CSS_URL . 'assets/images/vela-logo-white.svg' ); ?>" alt="<?php esc_attr_e( 'Vela', 'ko-accessiyes-css-customizations' ); ?>">
				</a>
				<div>
					<h1><?php esc_html_e( 'KO: AccessiYES CSS Customizations', 'ko-accessiyes-css-customizations' ); ?></h1>
					<p><?php esc_html_e( 'Manage CSS inside the accessibility widget’s isolated Shadow DOM.', 'ko-accessiyes-css-customizations' ); ?></p>
				</div>
			</header>

			<div class="ko-accessiyes-css-layout">
				<section class="ko-accessiyes-css-panel ko-accessiyes-css-editor">
					<h2><?php esc_html_e( 'AccessiYES CSS', 'ko-accessiyes-css-customizations' ); ?></h2>
					<p><?php esc_html_e( 'Enter plain CSS without style tags. The plugin waits for the AccessiYES widget, then injects these rules directly into its open Shadow DOM.', 'ko-accessiyes-css-customizations' ); ?></p>

					<form action="options.php" method="post">
						<?php settings_fields( self::SETTINGS_GROUP ); ?>
						<label class="screen-reader-text" for="ko-accessiyes-css-editor"><?php esc_html_e( 'AccessiYES CSS', 'ko-accessiyes-css-customizations' ); ?></label>
						<textarea
							id="ko-accessiyes-css-editor"
							name="<?php echo esc_attr( self::OPTION_KEY ); ?>"
							rows="32"
							spellcheck="false"
							wrap="off"
							aria-describedby="ko-accessiyes-css-help"
						><?php echo esc_textarea( is_string( $css ) ? $css : '' ); ?></textarea>
						<p id="ko-accessiyes-css-help" class="description"><?php esc_html_e( 'Clear the editor and save to stop all managed front-end CSS without deactivating the plugin.', 'ko-accessiyes-css-customizations' ); ?></p>
						<?php submit_button( __( 'Save CSS', 'ko-accessiyes-css-customizations' ) ); ?>
					</form>
				</section>

				<aside class="ko-accessiyes-css-sidebar">
					<section class="ko-accessiyes-css-panel">
						<h2><?php esc_html_e( 'Integration Status', 'ko-accessiyes-css-customizations' ); ?></h2>
						<p class="ko-accessiyes-css-status <?php echo $accessiyes_active ? 'is-active' : 'is-inactive'; ?>">
							<span aria-hidden="true"></span>
							<?php echo esc_html( $integration_status ); ?>
						</p>
						<p class="ko-accessiyes-css-status <?php echo $saved_css_is_ready ? 'is-active' : 'is-inactive'; ?>">
							<span aria-hidden="true"></span>
							<?php echo esc_html( $saved_css_is_ready ? __( 'Saved CSS is ready', 'ko-accessiyes-css-customizations' ) : __( 'The CSS editor is blank', 'ko-accessiyes-css-customizations' ) ); ?>
						</p>
					</section>

					<section class="ko-accessiyes-css-panel">
						<h2><?php esc_html_e( 'Why a Shadow DOM Bridge?', 'ko-accessiyes-css-customizations' ); ?></h2>
						<p><?php esc_html_e( 'AccessiYES renders its interface inside a Shadow DOM. Browser encapsulation prevents ordinary theme, Customizer, Divi, and page-level CSS from selecting the widget’s internal elements.', 'ko-accessiyes-css-customizations' ); ?></p>
						<p><?php esc_html_e( 'This plugin leaves the vendor files untouched and places the saved rules inside that Shadow DOM after the widget is ready.', 'ko-accessiyes-css-customizations' ); ?></p>
					</section>

					<section class="ko-accessiyes-css-panel">
						<h2><?php esc_html_e( 'Front-End Behavior', 'ko-accessiyes-css-customizations' ); ?></h2>
						<p><?php esc_html_e( 'No bridge script is loaded while the editor is blank. When CSS is saved, the script waits up to 20 seconds for the compatible widget host and button before adding one namespaced style element.', 'ko-accessiyes-css-customizations' ); ?></p>
					</section>

					<section class="ko-accessiyes-css-panel">
						<h2><?php esc_html_e( 'Support', 'ko-accessiyes-css-customizations' ); ?></h2>
						<p><a href="mailto:ko@velaagency.com"><?php esc_html_e( 'ko@velaagency.com', 'ko-accessiyes-css-customizations' ); ?></a><br><a href="https://velaagency.com" target="_blank" rel="noopener"><?php esc_html_e( 'velaagency.com', 'ko-accessiyes-css-customizations' ); ?></a></p>
					</section>
				</aside>
			</div>

			<footer class="ko-accessiyes-css-footer">
				<?php
				printf(
					/* translators: %s is the plugin version. */
					esc_html__( 'KO: AccessiYES CSS Customizations v%s', 'ko-accessiyes-css-customizations' ),
					esc_html( KO_ACCESSIYES_CSS_VERSION )
				);
				?>
			</footer>
		</div>
		<?php
	}
}

register_activation_hook( KO_ACCESSIYES_CSS_FILE, array( 'KO_AccessiYES_CSS_Customizations', 'activate' ) );
new KO_AccessiYES_CSS_Customizations();
