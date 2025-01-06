<?php

namespace MBDI\Templates;

class FileInput extends File {
	/**
	 * Convert selected value to labels, separated by comma
	 *
	 * @return string
	 */
	public function render(): string {
		$value = $this->get_value();

		if ( ! is_array( $value ) ) {
			$value = [ $value ];
		}

		if ( $this->raw ) {
			return $value[0];
		}

		$items_per_row = $this->attrs['items_per_row'] ?? 1;

		$output  = '<div class="mbdi-file-wrapper">';
		$output .= '<ul class="mbdi-file-group mbdi-grid mbdi-grid-cols-' . $items_per_row . '">';

		foreach ( $value as $file ) {
			$output .= '<li><a href="' . esc_url( $file ) . '" title="' . esc_attr( $file ) . '">' . $file . '</a></li>';
		}

		$output .= '</ul>';
		$output .= '</div>';

		return $output;
	}
}
