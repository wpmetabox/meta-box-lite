<?php
namespace MBB\Upgrade\Ver404;

use MetaBox\Support\Arr;

/**
 * Update field settings from data for AngularJS to React.
 */
class Field extends Base {
	public function update( &$field ) {
		$new_field = [];

		$names = [ 'attrs', 'js_options', 'query_args', 'options' ];
		foreach ( $field as $key => $value ) {
			if ( in_array( $key, $names ) ) {
				$value = $this->update_key_value( $value );
			}
			$new_field[ $key ] = $value;
		}

		Arr::change_key( $new_field, 'attrs', 'custom_settings' );

		$this->update_conditional_logic( $new_field, $field );

		// Field-specific parser.
		$func = "update_field_{$field['type']}";
		if ( method_exists( $this, $func ) ) {
			$this->$func( $new_field, $field );
		}

		$field           = $new_field;
		$field['_state'] = 'collapsed';
	}

	private function update_key_value( $value ) {
		if ( empty( $value ) || ! is_array( $value ) ) {
			return $value;
		}

		$new_value = [];
		foreach ( $value as $option ) {
			$id               = uniqid();
			$new_value[ $id ] = array_merge( [ 'id' => $id ], $option );
		}

		return $new_value;
	}

	private function update_field_key_value( &$new_field, $field ) {
		if ( empty( $field['placeholder'] ) ) {
			return;
		}

		$new_field['placeholder_key']   = Arr::get( $field, 'placeholder.key' );
		$new_field['placeholder_value'] = Arr::get( $field, 'placeholder.value' );
		unset( $new_field['placeholder'] );
	}

	private function update_field_tab( &$new_field, $field ) {
		Arr::change_key( $new_field, 'label', 'name' );
		$new_field['icon_type'] = 'dashicons';
		$new_field['icon']      = str_replace( 'dashicons-', '', $new_field['icon'] );
	}
}
