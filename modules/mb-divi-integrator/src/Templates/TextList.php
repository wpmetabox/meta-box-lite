<?php

namespace MBDI\Templates;

class TextList extends Base {
	public function render(): string {
		$value = $this->get_value();

		if ( empty( $value ) ) {
			return '';
		}

		if ( ! is_array( $value ) ) {
			$value = [ $value ];
		}

		$value = array_map( 'esc_html', $value );

		return '<ul><li>' . implode( '</li><li>', $value ) . '</li></ul>';
	}
}
