<?php

// Prevent loading this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'mb_comment_meta_load' ) ) {
	/**
	 * Hook to 'init' with priority 5 to make sure all actions are registered before Meta Box 4.9.0 runs
	 */
	add_action( 'init', 'mb_comment_meta_load', 5 );

	/**
	 * Load plugin files after Meta Box is loaded
	 */
	function mb_comment_meta_load() {
		if ( ! defined( 'RWMB_VER' ) || class_exists( 'MB_Comment_Meta_Box' ) ) {
			return;
		}

		require dirname( __FILE__ ) . '/inc/class-mb-comment-meta-loader.php';
		require dirname( __FILE__ ) . '/inc/class-mb-comment-meta-box.php';
		require dirname( __FILE__ ) . '/inc/class-rwmb-comment-storage.php';
		$loader = new MB_Comment_Meta_Loader;
		$loader->init();
	}
}
