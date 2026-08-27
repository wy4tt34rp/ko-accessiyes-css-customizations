<?php
/**
 * Uninstall KO: AccessiYES Widget Manager.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'ko_accessiyes_css_customizations' );
