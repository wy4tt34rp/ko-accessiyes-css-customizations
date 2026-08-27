<?php
/**
 * Uninstall KO: AccessiYES CSS Customizations.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'ko_accessiyes_css_customizations' );
