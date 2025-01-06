<?php

namespace MBDI\Templates;

class Image extends Base {
	/**
	 * Return first attachment URL so that it can be used in Divi.
	 *
	 * @return string
	 */
	public function render(): string {
		$value = $this->get_value_as_cloneable();
		// Render image when using dynamic content.
		if ( $this->raw ) {
			return $value[0][0]['full_url'] ?? '';
		}

		$items_per_row = $this->attrs['items_per_row'] ?? 1;

		$output = '<div class="mbdi-images-wrapper">';

		foreach ( $value as $images ) {
			$output .= '<ul class="mbdi-images-group mbdi-grid mbdi-grid-cols-' . $items_per_row . '">';

			foreach ( $images as $image ) {
				$output .= '<li><img src="' . esc_url( $image['full_url'] ) . '" alt="' . esc_attr( $image['alt'] ?? '' ) . '" /></li>';
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
				// Make sure it's not a single image.
				if ( isset( $value[0]['file'] ) ) {
					$value = [ $value ];
				}

				$value = array_map(function ( $images ) {
					return array_values(array_map(function ( $img ) {
						// If it's inside group, it contains only image ID, so we need to load image from ID.
						if ( is_numeric( $img ) ) {
							$url = wp_get_attachment_image_src( $img, 'full' );
							$img = [
								'full_url' => $url[0],
								'alt'      => get_post_meta( $img, '_wp_attachment_image_alt', true ),
							];
						}

						return [
							'full_url' => $img['full_url'],
							'alt'      => $img['alt'],
						];
					}, $images));
				}, $value);
			}
		}

		return $value;
	}
}
