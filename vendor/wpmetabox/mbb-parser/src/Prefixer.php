<?php
namespace MBBParser;

class Prefixer {
	public static function add( array &$fields, string $prefix ): void {
		if ( ! $prefix ) {
			return;
		}
		foreach ( $fields as &$field ) {
			if ( ! empty( $field['id'] ) && ! str_starts_with( $field['id'], $prefix ) ) {
				$field['id'] = $prefix . $field['id'];
			}
			if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
				self::add( $field['fields'], $prefix );
			}
		}
	}

	public static function remove( array &$fields, string $prefix ): void {
		if ( ! $prefix ) {
			return;
		}
		foreach ( $fields as &$field ) {
			if ( ! empty( $field['id'] ) && str_starts_with( $field['id'], $prefix ) ) {
				$field['id'] = substr( $field['id'], strlen( $prefix ) );
			}
			if ( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {
				self::remove( $field['fields'], $prefix );
			}
		}
	}
}
