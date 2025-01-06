<?php

// Prevent loading this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( file_exists( __DIR__ . '/vendor' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

new MBBTI\Posts;
new MBBTI\Terms;
new MBBTI\Settings;
new MBBTI\Authors;
new MBBTI\Users;
new MBBTI\ConditionalLogic;
