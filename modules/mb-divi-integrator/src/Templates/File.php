<?php

namespace MBDI\Templates;

class File extends Base {
	public function render(): string {
		$value = $this->get_value_as_cloneable();

		if ( $this->raw ) {
			return $value[0][0]['name'];
		}

		$items_per_row = $this->attrs['items_per_row'] ?? 1;

		$output = '<div class="mbdi-files-wrapper">';

		foreach ( $value as $files ) {
			$output .= '<ul class="mbdi-files-group mbdi-grid mbdi-grid-cols-' . $items_per_row . '">';

			foreach ( $files as $file ) {
				$output .= '<li><a href="' . esc_url( $file['url'] ) . '" title="' . esc_attr( $file['name'] ?? '' ) . '">' . $file['name'] . '</a></li>';
			}

			$output .= '</ul>';
		}

		$output .= '</div>';

		return $output;
	}

	private function get_value_as_cloneable() {
		$value = $this->get_value();

		// First we convert value to nested array of cloneable group and images
		if ( is_array( $value ) ) {
			// Make non-clonable field compatible with clonable field.
			if ( ! isset( $value[0] ) ) {
				$value = [ $value ];
			}

			// Make group field compatible with group cloneable field
			if ( is_numeric( $value[0] ) ) {
				$value = [ $value ];
			}

			// Non-group field returns an array of images and clonable. We need to re-index the array.
			if ( is_array( $value[0] ) ) {
				$value = array_map(function ( $file ) {
					return array_values(array_map(function ( $file ) {
						// If it's inside group, it contains only image ID, so we need to load image from ID.
						if ( is_numeric( $file ) ) {
							$url = wp_get_attachment_url( $file );

							$file = [
								'name' => $url[0],
								'url'  => $url[0],
							];
						}

						return [
							'name' => $file['name'],
							'url'  => $file['url'],
						];
					}, $file));
				}, $value);
			}
		}

		return $value;
	}
}
