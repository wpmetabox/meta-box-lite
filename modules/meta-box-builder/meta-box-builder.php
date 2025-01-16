<?php
/**
 * Plugin Name: Meta Box Builder
 * Plugin URI:  https://metabox.io/plugins/meta-box-builder/
 * Description: Drag and drop UI for creating custom meta boxes and custom fields.
 * Version:     4.9.7
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 */

// Prevent loading this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'mb_builder_load' ) ) {
	if ( file_exists( __DIR__ . '/vendor' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	}

	// Hook to 'init' with priority 0 to run all extensions (for registering settings pages & relationships).
	// And after MB Custom Post Type (for ordering submenu items in Meta Box menu).
	add_action( 'init', 'mb_builder_load', 0 );

	/**
	 * Load plugin files after Meta Box is loaded
	 */
	function mb_builder_load() {
		if ( ! defined( 'RWMB_VER' ) ) {
			return;
		}

		define( 'MBB_VER', '4.9.7' );
		define( 'MBB_DIR', trailingslashit( __DIR__ ) );

		list( , $url ) = \RWMB_Loader::get_path( MBB_DIR );
		define( 'MBB_URL', $url );

		require __DIR__ . '/bootstrap.php';
	}
}
