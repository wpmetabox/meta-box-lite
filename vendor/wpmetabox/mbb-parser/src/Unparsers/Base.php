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
