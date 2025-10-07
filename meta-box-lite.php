<?php
/**
 * Plugin Name: Meta Box Lite
 * Plugin URI:  https://metabox.io/pricing/
 * Description: A feature-rich free UI version of Meta Box.
 * Version:     2.2.0
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 * Text Domain: meta-box-lite
 *
 * Copyright (C) 2010-2025 Tran Ngoc Tuan Anh. All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

define( 'META_BOX_LITE_DIR', __DIR__ );

// Use 'plugins_loaded' hook to make sure it runs "after" individual extensions are loaded.
// So individual extensions can take a higher priority.
add_action( 'plugins_loaded', function (): void {
	require_once __DIR__ . '/vendor/autoload.php';
} );

// Load translations
add_action( 'init', function (): void {
	load_plugin_textdomain( 'meta-box', false, basename( __DIR__ ) . '/languages/meta-box' );
	load_plugin_textdomain( 'mb-custom-post-type', false, basename( __DIR__ ) . '/languages/mb-custom-post-type' );
} );
