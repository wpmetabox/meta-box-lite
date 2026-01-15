<?php
namespace MBBParser\Unparsers;

use MBBParser\SettingsTrait;
use MetaBox\Support\Arr;

class Base {
	use SettingsTrait;

	protected $empty_keys = [];

	public function __construct( $settings = [] ) {
		$this->settings = (array) $settings;
	}

	public function set_settings( $settings ) {
		$this->settings = (array) $settings;

		return $this;
	}

	public function unparse_boolean_values() {
		array_walk_recursive( $this->settings, [ $this, 'convert_string_to_boolean' ] );

		return $this;
	}

	protected function convert_string_to_boolean( &$value ) {
		if ( 'true' === $value ) {
			$value = true;
		} elseif ( 'false' === $value ) {
			$value = false;
		}
	}

	public function unparse_numeric_values() {
		array_walk_recursive( $this->settings, [ $this, 'convert_number_to_string' ] );
		return $this;
	}

	protected function convert_number_to_string( &$value ) {
		if ( is_numeric( $value ) && ! is_bool( $value ) ) {
			$value = (string) $value;
		}
	}

	protected function unparse_array_attributes( $key ) {
		$value = $this->$key;

		if ( ! is_array( $value ) ) {
			return $this;
		}

		// Convert nested arrays/objects to dot notation to prevent "[object Object]" issues.
		$value = $this->array_to_dot_notation( $value );

		$tmp_array = [];
		foreach ( $value as $k => $v ) {
			$tmp_key               = uniqid();
			$tmp_array[ $tmp_key ] = [
				'id'    => $tmp_key,
				'key'   => $k,
				'value' => $v,
			];
		}

		$this->$key = $tmp_array;

		return $this;
	}

	/**
	 * Convert nested arrays/objects to dot notation.
	 * Converts ['tax_query' => ['0' => ['taxonomy' => 'service-category']]]
	 * to ['tax_query.0.taxonomy' => 'service-category']
	 *
	 * @param array  $array The array to convert.
	 * @param string $prefix Internal use for recursion.
	 * @return array Converted array with dot notation keys.
	 */
	protected function array_to_dot_notation( array $array, string $prefix = '' ): array {
		$result = [];

		foreach ( $array as $key => $value ) {
			$new_key = $prefix === '' ? (string) $key : $prefix . '.' . $key;

			if ( is_array( $value ) && ! empty( $value ) ) {
				// Check if this is a numeric array (list) or associative array (object-like).
				$keys = array_keys( $value );
				$is_numeric = ! empty( $keys ) && $keys === array_values( range( 0, count( $value ) - 1 ) );

				if ( $is_numeric ) {
					// For numeric arrays (like tax_query[0], tax_query[1]), preserve numeric indices.
					foreach ( $value as $idx => $item ) {
						if ( is_array( $item ) && ! empty( $item ) ) {
							// Recursively convert nested structures within numeric arrays.
							$nested = $this->array_to_dot_notation( $item, $new_key . '.' . $idx );
							$result = array_merge( $result, $nested );
						} else {
							$result[ $new_key . '.' . $idx ] = $item;
						}
					}
				} else {
					// For associative arrays, recursively convert.
					$nested = $this->array_to_dot_notation( $value, $new_key );
					$result = array_merge( $result, $nested );
				}
			} else {
				// Convert non-array values to strings to prevent "[object Object]" issues.
				if ( is_bool( $value ) ) {
					$value = $value ? 'true' : 'false';
				} elseif ( is_null( $value ) ) {
					$value = '';
				} elseif ( ! is_scalar( $value ) ) {
					// For objects or other non-scalar types, convert to JSON string.
					$value = wp_json_encode( $value );
				}
				$result[ $new_key ] = $value;
			}
		}

		return $result;
	}

	protected function unparse_conditional_logic() {
		if ( empty( $this->visible ) && empty( $this->hidden ) ) {
			return $this;
		}

		if ( ! class_exists( 'MB_Conditional_Logic' ) ) {
			return $this;
		}

		$conditional_logic = ( new \MB_Conditional_Logic() )->parse_conditions( $this->settings );

		$output = [];
		foreach ( $conditional_logic as $action => $condition ) {
			$output['type']     = $action;
			$output['relation'] = $condition['relation'];
			$output['when']     = [];

			foreach ( $condition['when'] as $criteria ) {
				$name = $criteria[0]; // Use field name as key

				$output['when'][ $name ] = [
					'id'       => $name,
					'name'     => $name,
					'operator' => $criteria[1],
					'value'    => $criteria[2],
				];
			}
		}
		unset( $this->visible, $this->hidden );

		$this->conditional_logic = $output;

		return $this;
	}

	/**
	 * Inverse of remove_default.
	 *
	 * @param mixed $key    The key to add the default value to.
	 * @param mixed $value  The default value to add.
	 * @return static
	 */
	protected function add_default( $key, $value ) {
		if ( ! isset( $this->$key ) ) {
			$this->$key = $value;
		}

		return $this;
	}

	/**
	 * Lookup from the data using keys, return the first key found or the fallback value.
	 *
	 * @param array $keys     Array of keys to lookup.
	 * @param mixed $fallback Fallback value to return if no key is found.
	 * @return mixed The value of the first key found or the fallback value.
	 */
	public function lookup( array $keys, $fallback = null ) {
		foreach ( $keys as $key ) {
			if ( Arr::get( $this->settings, $key ) !== null ) {
				return Arr::get( $this->settings, $key );
			}
		}

		return $fallback;
	}
}
