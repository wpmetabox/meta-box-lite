<?php
/**
 * Plugin Name: MB FacetWP Integration
 * Plugin URI:  https://metabox.io/plugins/mb-facetwp-integrator/
 * Description: Integrates Meta Box custom fields with FacetWP.
 * Author:      MetaBox.io
 * Version:     1.1.6
 * Author URI:  https://metabox.io
 * License:     GPL-2.0
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

if ( ! class_exists( 'MB_FacetWP_Integrator' ) ) {
	require __DIR__ . '/class-mb-facetwp-integrator.php';
	new MB_FacetWP_Integrator;

	add_action( 'mb_relationships_init', function () {
		add_filter( 'facetwp_facet_types', function ($types) {
			require_once __DIR__ . '/class-mb-relationships-facetwp.php';

			$types[ MB_Relationships_FacetWP::FACET_TYPE ] = new MB_Relationships_FacetWP();
			return $types;
		} );
	} );
}


