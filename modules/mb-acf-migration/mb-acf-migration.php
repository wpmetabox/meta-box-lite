<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'mb_acf_load' ) ) {
	if ( file_exists( __DIR__ . '/vendor' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	}

	add_action( 'init', 'mb_acf_load', 0 );

	function mb_acf_load() {
		if ( ! defined( 'RWMB_VER' ) || ! class_exists( 'ACF' ) || ! is_admin() ) {
			return;
		}

		define( 'MBACF_DIR', __DIR__ );

		new MetaBox\ACF\AdminPage();
		new MetaBox\ACF\Ajax();
	}
}
