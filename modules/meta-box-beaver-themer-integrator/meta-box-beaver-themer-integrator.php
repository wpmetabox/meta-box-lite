<?php
/**
 * Plugin Name: MB Beaver Builder Integration
 * Plugin URI:  https://metabox.io/plugins/meta-box-beaver-themer-integrator/
 * Description: Integrates Meta Box and Beaver Builder.
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * Text Domain: meta-box-beaver-themer-integrator
 * Version:     2.1.5
 * License:     GPL-2.0
 */

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
