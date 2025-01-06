<?php

namespace MBDI\Templates;

class Text extends Base {
	public function render(): string {
		if ( is_string( $this->get_value() ) ) {
			return $this->get_value();
		}

		// cloneable text
		if ( is_array( $this->get_value() ) ) {
			return implode( ', ', $this->get_value() );
		}

		return '';
	}
}
