<?php
/*
 * Plugin Name: Custom Posts Per Page
 * Plugin URI: https://github.com/jeremyfelt/Custom-Posts-Per-Page
 * Description: Shows a custom number of posts depending on the type of page being viewed.
 * Version: 2.0.0
 * Author: Jeremy Felt
 * Author URI: https://jeremyfelt.com
 * Text Domain: custom-posts-per-page
 * Domain Path: /languages
 * License: GPL2
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// This plugin, like WordPress, requires PHP 5.6 and higher.
if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
	add_action( 'admin_notices', 'cpppc_admin_notice' );
	/**
	 * Display an admin notice if PHP is not 5.6.
	 */
	function cpppc_admin_notice() {
		echo '<div class=\"error\"><p>';
		echo __( 'The Custom Posts Per Page WordPress plugin requires PHP 5.6 to function properly. Please upgrade PHP or deactivate the plugin.', 'custom-posts-per-page' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</p></div>';
	}

	return;
}

require_once __DIR__ . '/includes/main.php';
require_once __DIR__ . '/includes/query.php';
require_once __DIR__ . '/includes/settings.php';
