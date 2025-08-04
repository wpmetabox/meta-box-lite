<?php
namespace MBB\Extensions\Blocks\Json;

use MBB\Helpers\Path;

class Generator {
	public function __construct() {
		add_action( 'mbb_after_save', [ $this, 'generate_block_json' ], 10, 3 );
	}

	public function generate_block_json( $parser, $post_id, $raw_data ): void {
		$settings = $parser->get_settings();

		// Bail if this is not a block.
		if ( ! isset( $settings['type'] ) || $settings['type'] !== 'block' ) {
			return;
		}

		// Bail if block path is empty.
		if ( empty( $settings['block_json'] ) || ! $settings['block_json']['enable'] ) {
			return;
		}

		$this->generate_block_package( $settings, $post_id, $raw_data );
	}

	private function generate_block_metadata( array $settings, array $raw_data ): array {
		$block_id = $settings['id'] ?? sanitize_title( $settings['title'] );

		$metadata = [
			'$schema'     => 'https://schemas.wp.org/trunk/block.json',
			'apiVersion'  => 3,
			'version'     => 'v' . time(),
			'name'        => "meta-box/{$block_id}",
			'title'       => $settings['title'] ?? '',
			'description' => $settings['description'] ?? '',
			'category'    => $settings['category'] ?? 'common',
			'icon'        => $settings['icon'] ?? $settings['icon_svg'] ?? 'admin-generic',
			'keywords'    => $settings['keywords'] ?? [],
			'supports'    => [
				'html'   => false,
				'anchor' => false,
				'align'  => true,
			],
		];

		if ( ! empty( $settings['render_callback'] ) && str_starts_with( $settings['render_callback'], 'view:' ) ) {
			$metadata['render'] = $settings['render_callback'];
		}

		// Add fields to block metadata attributes.
		$attributes = [];
		if ( isset( $raw_data['fields'] ) && is_array( $raw_data['fields'] ) ) {
			$attributes = $this->generate_block_attributes( $raw_data['fields'] );
		}

		$align = array_filter( $settings['supports']['align'] ?? [] );
		// Alignments
		if ( ! empty( $align ) ) {
			$metadata['supports']['align'] = $align;
			$attributes['align']           = [
				'type' => 'string',
			];
		}

		$metadata['attributes'] = ! empty( $attributes ) ? $attributes : new \stdClass();

		return $metadata;
	}

	/**
	 * Generate block metadata attribute
	 *
	 * If field is multiple or clone, then field type is array.
	 * If field type is number, then field type is number.
	 * Otherwise, field type is string.
	 *
	 * @todo: Add support for other field types. For example, enum.
	 */
	private function generate_block_attributes( ?array $fields ) {
		if ( ! is_array( $fields ) ) {
			return [];
		}

		$attributes = [];

		foreach ( $fields as $field ) {
			$id       = $field['id'] ?? $field['_id'] ?? null;
			$type_std = $this->get_field_type_and_default_value( $field );

			if ( is_null( $type_std ) ) {
				continue;
			}

			[ $type, $std ] = $type_std;

			$attributes[ $id ] = [
				'type'           => $type,
				'meta-box-field' => $field,
			];

			if ( $std ) {
				$attributes[ $id ]['default'] = $std;
			}
		}

		return $attributes;
	}

	private function get_field_type_and_default_value( $field ) {
		$type = 'string';
		$std  = $field['std'] ?? null;

		// These fields returns array
		$array_fields = [
			'group',
			'checkbox_list',
			'file_advanced',
			'autocomplete',
			'file_upload',
			'file',
			'image',
			'image_advanced',
			'image_upload',
			'key_value',
		];

		$field['id'] = $field['id'] ?? $field['_id'] ?? null;

		if ( ! isset( $field['type'] ) || ! isset( $field['id'] ) ) {
			return;
		}

		if ( in_array( $field['type'], [ 'number', 'slider', 'range' ] ) ) {
			$type = 'number';
			$std  = is_numeric( $field['std'] ) ? $field['std'] : 0;
		}

		if ( in_array( $field['type'], [ 'checkbox', 'switch' ] ) ) {
			$type = 'boolean';
			$std  = isset( $field['std'] ) ? (bool) $field['std'] : false;
		}

		if ( in_array( $field['type'], [ 'single_image', 'file_input', 'user', 'post' ] ) ) {
			$type = 'object';
			$std  = new \stdClass();
		}

		$is_multiple = ( isset( $field['multiple'] ) && $field['multiple'] )
			|| ( isset( $field['type'] ) && in_array( $field['type'], $array_fields ) )
			|| ( isset( $field['field_type'] ) && in_array( $field['field_type'], [ 'select_tree', 'checkbox_tree', 'checkbox_list', 'checkbox_tree' ] ) );

		$is_cloneable = $field['clone'] ?? false;

		if ( $is_multiple || $is_cloneable ) {
			$type = 'array';
			$std  = ! empty( $field['std'] ) && is_array( $field['std'] ) ? $field['std'] : [];
		}

		return [ $type, $std ];
	}

	private function generate_block_package( array $settings, $post_id, $raw_data ): void {
		$block_id          = $settings['id'];
		$block_path        = trailingslashit( $settings['block_json']['path'] ) . $block_id;
		$parent_block_path = dirname( $block_path );

		if ( ! Path::is_future_path_writable( $parent_block_path ) ) {
			return;
		}

		if ( ! file_exists( $block_path ) ) {
			wp_mkdir_p( $block_path );
		}

		$new_metadata = $this->generate_block_metadata( $settings, $raw_data );

		// Compare old and new block metadata, and save the new one if it's newer.
		$block_json_path = $block_path . '/block.json';

		$is_newer = false;

		if ( ! file_exists( $block_json_path ) ) {
			$is_newer = true;
		} else {
			$current_metadata = json_decode( file_get_contents( $block_json_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			foreach ( $new_metadata as $key => $value ) {
				if ( $key === 'version' ) {
					continue;
				}

				if ( ! isset( $current_metadata[ $key ] ) || $current_metadata[ $key ] !== $value ) {
					$is_newer = true;
					break;
				}
			}
		}

		if ( ! $is_newer ) {
			return;
		}

		// Save the new version back to the post meta.
		$settings                          = get_post_meta( $post_id, 'settings', true );
		$settings['block_json']['version'] = $new_metadata['version'];
		update_post_meta( $post_id, 'settings', $settings );

		// phpcs:disable
		$new_metadata = json_encode( $new_metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		file_put_contents( $block_json_path, $new_metadata );
		chmod( $block_json_path, 0664 );
		// phpcs:enable
	}
}
