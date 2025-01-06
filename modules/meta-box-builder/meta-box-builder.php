<?php

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
