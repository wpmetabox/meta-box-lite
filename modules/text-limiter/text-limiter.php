<?php
/**
 * Plugin Name: MB Text Limiter
 * Plugin URI:  https://metabox.io/plugins/meta-box-text-limiter/
 * Description: Limit number of characters or words entered for text, textarea, and wysiwyg fields.
 * Version:     1.2.7
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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

if ( ! class_exists( 'MB_Text_Limiter' ) ) {
	class MB_Text_Limiter {
		/**
		 * List of supported fields.
		 *
		 * @var array
		 */
		protected $types = [ 'text', 'textarea', 'wysiwyg' ];

		public function init() {
			add_action( 'rwmb_before', [ $this, 'register' ] );

			// Change the output of fields with limit.
			add_filter( 'rwmb_get_value', [ $this, 'get_value' ], 10, 2 );
			add_filter( 'rwmb_the_value', [ $this, 'get_value' ], 10, 2 );

			add_action( 'rwmb_enqueue_scripts', [ $this, 'enqueue' ] );
		}

		/**
		 * Register hook to change the output of text/textarea fields.
		 */
		public function register() {
			foreach ( $this->types as $type ) {
				add_filter( "rwmb_{$type}_html", [ $this, 'show' ], 10, 2 );
			}
		}

		/**
		 * Change the output of text/textarea fields.
		 *
		 * @param string $output HTML output of the field.
		 * @param array  $field  Field parameter.
		 *
		 * @return string
		 */
		public function show( $output, $field ) {
			if ( ! isset( $field['limit'] ) || ! is_numeric( $field['limit'] ) || ! $field['limit'] > 0 ) {
				return $output;
			}

			$type = $field['limit_type'] ?? 'character';
			$text = 'word' === $type ? __( 'Word Count', 'text-limiter' ) : __( 'Character Count', 'text-limiter' );

			return $output . '
				<div class="text-limiter" data-limit-type="' . esc_attr( $type ) . '">
					<span>' . esc_html( $text ) . ':
						<span class="counter">0</span>/<span class="maximum">' . esc_html( $field['limit'] ) . '</span>
					</span>
				</div>';
		}

		/**
		 * Filters the value of a field
		 *
		 * @see rwmb_get_field() in meta-box/inc/functions.php for explenation
		 *
		 * @param string $value Field value.
		 * @param array  $field Field parameters.
		 *
		 * @return string
		 */
		public function get_value( $value, $field ) {
			if ( empty( $field ) ) {
				return $value;
			}

			if ( ! in_array( $field['type'], $this->types, true ) || empty( $field['limit'] ) || ! is_numeric( $field['limit'] ) ) {
				return $value;
			}

			if ( ! is_string( $value ) ) {
				return $value;
			}

			// Don't truncate if $value contains HTML.
			if ( str_contains( $value, '<' ) ) {
				return $value;
			}

			$type = isset( $field['limit_type'] ) ? $field['limit_type'] : 'character';
			if ( 'character' === $type ) {
				return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $field['limit'] ) : substr( $value, 0, $field['limit'] );
			}

			$value = preg_split( '/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY );
			$value = implode( ' ', array_slice( $value, 0, $field['limit'] ) );

			return $value;
		}

		public function enqueue() {
			// Use helper function to get correct URL to current folder, which can be used in themes/plugins.
			list( , $url ) = RWMB_Loader::get_path( __DIR__ );

			wp_enqueue_style( 'text-limiter', $url . 'text-limiter.css', [], filemtime( __DIR__ . '/text-limiter.css' ) );
			wp_enqueue_script( 'text-limiter', $url . 'text-limiter.js', [ 'jquery' ], filemtime( __DIR__ . '/text-limiter.js' ), true );
		}
	}

	$text_limiter = new MB_Text_Limiter();
	$text_limiter->init();
}
