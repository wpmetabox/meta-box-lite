<?php
namespace MBB\Relationships\Parsers;

use MetaBox\Support\Arr;
use MBBParser\Parsers\Base;

class Relationship extends Base {
	public function parse() {
		// Set extra params from post title and post name.
		$this->settings = array_merge( [
			'id' => rwmb_request()->post( 'post_name' ),
		], $this->settings );

		$this->parse_boolean_values()
			->parse_side( 'from' )
			->parse_side( 'to' );
	}

	private function parse_side( $side ) {
		$settings = &$this->settings[ $side ];

		$object_type = Arr::get( $settings, 'object_type', 'post' );
		if ( 'post' === $object_type ) {
			unset( $settings['taxonomy'] );
		}
		if ( 'term' === $object_type ) {
			unset( $settings['post_type'] );
		}
		if ( 'user' === $object_type ) {
			unset( $settings['post_type'] );
			unset( $settings['taxonomy'] );
		}

		if ( empty( $settings['empty_message'] ) ) {
			unset( $settings['empty_message'] );
		}

		$this->parse_admin_column( $settings );

		// Meta box settings.
		$meta_box_parser = new MetaBox( $settings['meta_box'] );
		$meta_box_parser->parse();
		$settings['meta_box'] = $meta_box_parser->get_settings();
		if ( empty( $settings['meta_box'] ) ) {
			unset( $settings['meta_box'] );
		}

		// Field settings.
		$field_parser = new Field( $settings['field'] );
		$field_parser->parse();
		$settings['field'] = $field_parser->get_settings();
		if ( empty( $settings['field'] ) ) {
			unset( $settings['field'] );
		}

		// Shorten the syntax if possible.
		if ( 2 === count( $settings ) && 'post' === $settings['object_type'] ) {
			$settings = $settings['post_type'];
		}

		return $this;
	}

	private function parse_admin_column( &$settings ) {
		$enable = Arr::get( $settings, 'admin_column.enable', false );
		if ( ! $enable ) {
			unset( $settings['admin_column'] );
			return $settings;
		}

		$admin_column = &$settings['admin_column'];
		unset( $admin_column['enable'] );
		$admin_column['position'] = trim( implode( ' ', $admin_column['position'] ) );
		foreach ( $admin_column as $key => $value ) {
			if ( '' === $value ) {
				unset( $admin_column[ $key ] );
			}
		}
		if ( empty( $admin_column ) ) {
			$admin_column = true;
		}
		if ( 1 === count( $admin_column ) && isset( $admin_column['position'] ) ) {
			$admin_column = $admin_column['position'];
		}

		return $settings;
	}
}
