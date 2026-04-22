<?php
namespace MBB\Helpers;

use MBB\RestApi\Fields;

/**
 * Provide field setting keys per field type, used by mbb-parser to detect custom keys on import.
 */
class FieldKeys {

	/**
	 * List of field setting keys per field type [ type => [key, ...] ].
	 * @var array<string, string[]>|null 
	 */
	private static ?array $keys_by_type = null;

	/**
	 * All keys across every field type.
	 * @var string[]|null
	 */
	private static ?array $all_keys = null;

	private static ?Fields $fields_api = null;

	/**
	 * Inject the existing Fields instance.
	 */
	public static function init( Fields $fields_api ): void {
		self::$fields_api = $fields_api;
	}

	public static function all(): array {

		if ( self::$all_keys !== null ) {
			return self::$all_keys;
		}

		self::build();

		self::$all_keys = array_values( array_unique( array_merge( ...array_values( self::$keys_by_type ) ) ) );
		return self::$all_keys;
	}


	private static function build(): void {
		if ( self::$fields_api === null ) {
			return;
		}

		if ( self::$keys_by_type !== null ) {
			return;
		}

		$field_types  = self::$fields_api->get_field_types();
		$keys_by_type = [];

		foreach ( $field_types as $type => $field_type ) {
			$keys = [];

			foreach ( $field_type['controls'] ?? [] as $control ) {
				if ( ! is_array( $control ) || ! isset( $control['setting'] ) ) {
					continue;
				}

				$keys[] = $control['setting'];

				// Compound controls (InputGroup) expose actual JSON keys via props (e.g. min, max).
				$props = $control['props'] ?? [];
				if ( isset( $props['key1'] ) ) {
					$keys[] = $props['key1'];
				}
				if ( isset( $props['key2'] ) ) {
					$keys[] = $props['key2'];
				}
			}

			// Expand virtual control keys to their actual JSON sub-keys.
			$keys = self::expand_virtual_keys( $keys );

			$keys_by_type[ $type ] = array_unique( $keys );
		}

		self::$keys_by_type = $keys_by_type;
	}

	/**
	 * Expand virtual control keys (e.g. clone_settings, text_limiter) to their actual JSON keys.
	 */
	private static function expand_virtual_keys( array $keys ): array {
		$virtual_map = [
			'clone_settings' => [
				'clone',
				'sort_clone',
				'clone_default',
				'clone_as_multiple',
				'min_clone',
				'max_clone',
				'add_button',
				'clone_empty_start',
			],
			'text_limiter'   => [ 'limit', 'limit_type' ],
		];

		$result = [];
		foreach ( $keys as $key ) {
			$result[] = $key;
			if ( isset( $virtual_map[ $key ] ) ) {
				foreach ( $virtual_map[ $key ] as $sub_key ) {
					$result[] = $sub_key;
				}
			}
		}

		return $result;
	}
}
