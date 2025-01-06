<?php

// Prevent loading this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'MBDI_PATH' ) ) {
	if ( file_exists( __DIR__ . '/vendor' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	}

	define( 'MBDI_PATH', plugin_dir_path( __FILE__ ) );

	new MBDI\Main;
}
