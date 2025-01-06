<?php

namespace MBDI\Templates;

class Select extends Base {
	/**
	 * Convert selected value to labels, separated by comma
	 *
	 * @return string
	 */
	public function render(): string {
		$value = $this->get_value();

		if ( empty( $value ) ) {
			return '';
		}

		if ( ! is_array( $value ) ) {
			$value = [ $value ];
		}

		$field = $this->get_field();

		$options = $field['options'];

		$value = array_map( function ( $value ) use ( $options ) {
			return $options[ $value ] ?? $value;
		}, $value );

		return implode( ', ', $value );
	}
}
