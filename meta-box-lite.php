<?php
/**
 * Plugin Name: Meta Box Lite
 * Plugin URI:  https://metabox.io/pricing/
 * Description: A feature-rich free UI version of Meta Box.
 * Version:     1.0.1
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 * Text Domain: meta-box-lite
 */

if ( function_exists( 'add_action' ) ) {
	// Use 'plugins_loaded' hook to make sure it runs "after" individual extensions are loaded.
	// So individual extensions can take a higher priority.
	add_action( 'plugins_loaded', function () {
		require_once __DIR__ . '/vendor/autoload.php';
	} );
}
