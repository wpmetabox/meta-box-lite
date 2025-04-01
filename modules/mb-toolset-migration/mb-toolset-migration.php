<?php
/**
 * Plugin Name: MB Toolset Migration
 * Plugin URI:  https://metabox.io/plugins/mb-toolset-migration
 * Description: Migrate Toolset custom fields to Meta Box.
 * Version:     1.0.6
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 * Text Domain: mb-toolset-migration
 */

// Prevent loading this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'mb_toolset_load' ) ) {
	if ( file_exists( __DIR__ . '/vendor' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	}

	add_action( 'init', 'mb_toolset_load', 0 );

	function mb_toolset_load() {
		if ( ! defined( 'RWMB_VER' ) || ! defined( 'TYPES_VERSION' ) || ! is_admin() ) {
			return;
		}

		define( 'MBTS_DIR', __DIR__ );

		new MetaBox\TS\AdminPage();
		new MetaBox\TS\Ajax();
	}
}
