<?php

namespace MBDI\Templates;

class Background extends Base {
	public function render(): string {
		$background = $this->get_value();

		if ( empty( $background ) ) {
			return '';
		}

		$return = '';

		if ( isset( $background['image'] ) && ! empty( $background['image'] ) ) {
			$return .= 'background: url(\'' . $background['image'] . '\') ' . $background['position'] . ' / ' . $background['size'] . ' ' . $background['repeat'];
		} else {
			$return .= 'background: ' . $background['color'];
		}

		$return .= ' ' . $background['attachment'] . ';';

		return $return;
	}
}
