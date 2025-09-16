<?php
/**
 * Plugin Name: MB Toolset Migration
 * Plugin URI:  https://metabox.io/plugins/mb-toolset-migration
 * Description: Migrate Toolset custom fields to Meta Box.
 * Version:     1.0.7
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 * Text Domain: mb-toolset-migration
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 */

// Prevent loading this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'mb_toolset_load' ) ) {
	if ( file_exists( __DIR__ . '/vendor' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	}

	add_action( 'init', 'mb_toolset_load', 0 );

	function mb_toolset_load() {
		if ( ! defined( 'RWMB_VER' ) || ! defined( 'TYPES_VERSION' ) || ! is_admin() ) {
			return;
		}

		define( 'MBTS_DIR', __DIR__ );

		new MetaBox\TS\AdminPage();
		new MetaBox\TS\Ajax();
	}
}
