<?php
namespace MBB\Integrations\WPML;

use WP_Post;

class FieldGroup {
	private $keys = [];

	public function __construct() {
		// List of keys for field that need to be translated.
		// See mbb-parser/src/Encoders/Field.php for the list of keys.
		$this->keys = [
			'name'              => __( 'Label', 'meta-box-builder' ),
			'desc'              => __( 'Description', 'meta-box-builder' ),
			'label_description' => __( 'Label description', 'meta-box-builder' ),
			'add_button'        => __( 'Add more text', 'meta-box-builder' ),
			'placeholder'       => __( 'Placeholder', 'meta-box-builder' ),
			'prefix'            => __( 'Prefix', 'meta-box-builder' ),
			'suffix'            => __( 'Suffix', 'meta-box-builder' ),
			'before'            => __( 'Before', 'meta-box-builder' ),
			'after'             => __( 'After', 'meta-box-builder' ),
			'std'               => __( 'Default value', 'meta-box-builder' ),
			'group_title'       => __( 'Group title', 'meta-box-builder' ),
			'prepend'           => __( 'Prepend', 'meta-box-builder' ),
			'append'            => __( 'Append', 'meta-box-builder' ),
		];

		add_action( 'save_post_meta-box', [ $this, 'register_package' ], 20, 2 );
		add_filter( 'mbb_meta_box', [ $this, 'use_translations' ], 10, 2 );
		add_action( 'deleted_post_meta-box', [ $this, 'delete_package' ], 10, 2 );
	}

	public function register_package( int $post_id, WP_Post $post ): void {
		$meta_box = get_post_meta( $post_id, 'meta_box', true );
		if ( empty( $meta_box ) || ! is_array( $meta_box ) ) {
			return;
		}

		$package = $this->get_package( $post );

		do_action( 'wpml_start_string_package_registration', $package );

		$this->register_strings( $meta_box, $post );

		do_action( 'wpml_delete_unused_package_strings', $package );
	}

	private function register_strings( array $meta_box, WP_Post $post ): void {
		$package = $this->get_package( $post );

		do_action(
			'wpml_register_string',
			$meta_box['title'] ?? '',
			'title',
			$package,
			__( 'Title', 'meta-box-builder' ),
			'LINE'
		);

		if ( ! empty( $meta_box['tabs'] ) ) {
			foreach ( $meta_box['tabs'] as $key => $tab ) {
				do_action(
					'wpml_register_string',
					$tab['label'] ?? '',
					'tab_' . $key,
					$package,
					sprintf( __( 'Tab: %s', 'meta-box-builder' ), $tab['label'] ?? '' ),
					'LINE'
				);
			}
		}

		$this->register_fields_strings( $meta_box['fields'], $package );
	}

	private function register_fields_strings( array $fields, array $package, string $base_id = '' ): void {
		foreach ( $fields as $index => $field ) {
			$this->register_field_strings( $field, $package, $base_id, $index );
		}
	}

	private function register_field_strings( array $field, array $package, string $base_id = '', int $index = 0 ): void {
		$field_id = $this->get_field_id( $field, $index );
		$id       = $base_id ? $base_id . '_' . $field_id : $field_id;

		foreach ( $this->keys as $key => $label ) {
			do_action(
				'wpml_register_string',
				$field[ $key ] ?? '',
				$id . '_' . $key,
				$package,
				// translators: %1$s is the field name, %2$s is the key.
				sprintf( '%1$s: %2$s', $field['name'], $label ),
				'LINE'
			);
		}

		if ( in_array( $field['type'], [ 'select', 'radio', 'checkbox_list', 'select_advanced', 'button_group', 'image_select', 'autocomplete' ], true ) ) {
			$options = $field['options'] ?? [];
			foreach ( $options as $key => $option ) {
				if ( is_string( $option ) ) {
					do_action(
						'wpml_register_string',
						$option,
						$id . '_option_' . $key,
						$package,
						// translators: %1$s is the field name, %2$s is the option label.
						sprintf( __( '%1$s: Option %2$s', 'meta-box-builder' ), $field['name'], $option ),
						'LINE'
					);
				}
			}
		}

		if ( isset( $field['tooltip'] ) ) {
			$tooltip = '';
			if ( is_string( $field['tooltip'] ) ) {
				$tooltip = $field['tooltip'];
			} elseif ( isset( $field['tooltip']['content'] ) ) {
				$tooltip = $field['tooltip']['content'];
			}

			do_action(
				'wpml_register_string',
				$tooltip,
				$id . '_tooltip',
				$package,
				// translators: %s is the field name.
				sprintf( __( '%s: Tooltip', 'meta-box-builder' ), $field['name'] ),
				'LINE'
			);
		}

		if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
			$this->register_fields_strings( $field['fields'], $package, $id );
		}
	}

	/**
	 * Filter to modify the meta box to use translations.
	 *
	 * @param array                                             $meta_box Meta box settings.
	 * @param object{'post_name': string, 'post_title': string} $post     Post object. Can't use WP_Post because when using Local JSON, the post object is not available.
	 *
	 * @return array Modified meta box settings.
	 */
	public function use_translations( array $meta_box, object $post ): array {
		$package = $this->get_package( $post );

		if ( ! empty( $meta_box['title'] ) ) {
			$meta_box['title'] = apply_filters( 'wpml_translate_string', $meta_box['title'], 'title', $package );
		}

		if ( ! empty( $meta_box['tabs'] ) ) {
			foreach ( $meta_box['tabs'] as $key => &$tab ) {
				if ( ! empty( $tab['label'] ) ) {
					$tab['label'] = apply_filters( 'wpml_translate_string', $tab['label'], 'tab_' . $key, $package );
				}
			}
		}

		$this->use_fields_translations( $meta_box['fields'], $package );

		return $meta_box;
	}

	private function use_fields_translations( array &$fields, array $package, string $base_id = '' ): void {
		foreach ( $fields as $index => &$field ) {
			$this->use_field_translations( $field, $package, $base_id, $index );
		}
	}

	private function use_field_translations( array &$field, array $package, string $base_id = '', int $index = 0 ): void {
		$field_id = $this->get_field_id( $field, $index );
		$id       = $base_id ? $base_id . '_' . $field_id : $field_id;

		foreach ( $this->keys as $key => $label ) {
			if ( ! empty( $field[ $key ] ) ) {
				$field[ $key ] = apply_filters( 'wpml_translate_string', $field[ $key ], $id . '_' . $key, $package );
			}
		}

		if ( in_array( $field['type'], [ 'select', 'radio', 'checkbox_list', 'select_advanced', 'button_group', 'image_select', 'autocomplete' ], true ) ) {
			$options = $field['options'] ?? [];
			foreach ( $options as $key => $option ) {
				$options[ $key ] = apply_filters( 'wpml_translate_string', $option, $id . '_option_' . $key, $package );
			}
			$field['options'] = $options;
		}

		if ( isset( $field['tooltip'] ) ) {
			$tooltip = '';
			if ( is_string( $field['tooltip'] ) ) {
				$tooltip = $field['tooltip'];
			} elseif ( isset( $field['tooltip']['content'] ) ) {
				$tooltip = $field['tooltip']['content'];
			}
			if ( ! empty( $tooltip ) ) {
				$field['tooltip'] = apply_filters( 'wpml_translate_string', $tooltip, $id . '_tooltip', $package );
			}
		}

		if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
			$this->use_fields_translations( $field['fields'], $package, $id );
		}
	}

	/**
	 * Get the package for the post.
	 *
	 * @param object{'post_name': string, 'post_title': string} $post Post object. Can't use WP_Post because when using Local JSON, the post object is not available.
	 *
	 * @return array Package.
	 */
	private function get_package( object $post ): array {
		return [
			'kind'  => 'Meta Box: Field Group',
			'name'  => urldecode( $post->post_name ),
			'title' => $post->post_title,
		];
	}

	public function delete_package( int $post_id, WP_Post $post ) {
		$package = $this->get_package( $post );
		do_action( 'wpml_delete_package', $package['name'], $package['kind'] );
	}

	private function get_field_id( array $field, int $index = 0 ): string {
		return empty( $field['id'] ) ? sanitize_key( $field['type'] ) . '_' . $index : $field['id'];
	}
}
