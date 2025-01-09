<?php
/**
 * Plugin Name: Meta Box Lite
 * Plugin URI:  https://metabox.io/pricing/
 * Description: A feature-rich free UI version of Meta Box.
 * Version:     0.0.2
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 * Text Domain: meta-box-lite
 */

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

$mbl_update_checker = PucFactory::buildUpdateChecker(
	'https://github.com/wpmetabox/meta-box-lite/',
	__FILE__,
	'meta-box-lite'
);
$mbl_update_checker->setBranch( 'master' );

$mbl_update_checker->addResultFilter( function ( $info, $response = null ): void {
	$info->icons = [
		'default' => 'https://i0.wp.com/metabox.io/wp-content/uploads/2015/08/logo.png',
	];
} );