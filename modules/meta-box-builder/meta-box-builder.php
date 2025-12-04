<?php
/**
 * Plugin Name: MB Builder
 * Plugin URI:  https://metabox.io/plugins/meta-box-builder/
 * Description: Drag and drop UI for creating custom meta boxes and custom fields.
 * Version:     5.1.6
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL-2.0-or-later
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
	function mb_builder_load(): void {
		if ( ! defined( 'RWMB_VER' ) ) {
			return;
		}

		define( 'MBB_VER', '5.1.6' );
		define( 'MBB_DIR', trailingslashit( __DIR__ ) );

		list( , $url ) = \RWMB_Loader::get_path( MBB_DIR );
		define( 'MBB_URL', $url );

		require __DIR__ . '/bootstrap.php';
	}
}
