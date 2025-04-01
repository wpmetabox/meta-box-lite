<?php
/**
 * Plugin Name: MB Pods Migration
 * Plugin URI:  https://metabox.io/plugins/mb-pods-migration
 * Description: Migrate Pods custom fields to Meta Box.
 * Version:     1.0.4
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 * Text Domain: mb-pods-migration
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'mb_pods_load' ) ) {
	if ( file_exists( __DIR__ . '/vendor' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	}

	add_action( 'init', 'mb_pods_load', 0 );

	function mb_pods_load() {
		if ( ! defined( 'RWMB_VER' ) || ! defined( 'PODS_DIR' ) || ! is_admin() ) {
			return;
		}

		define( 'MBPODS_DIR', __DIR__ );

		new MetaBox\Pods\AdminPage();
		new MetaBox\Pods\Ajax();
	}
}
