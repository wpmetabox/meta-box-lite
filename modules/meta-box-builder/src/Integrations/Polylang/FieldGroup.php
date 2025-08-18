<?php
namespace MBB\Integrations\Polylang;

use WP_Post;

class FieldGroup {
	private $keys = [];

	public function __construct() {
		// List of keys for field that need to be translated.
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

		add_filter( 'mbb_meta_box', [ $this, 'register_strings' ], 10 );
		add_filter( 'mbb_meta_box', [ $this, 'use_translations' ], 20, 2 );
	}

	public function register_strings( array $meta_box ): array {
		if ( empty( $meta_box ) || ! is_array( $meta_box ) ) {
			return $meta_box;
		}

		$context = $this->get_context( $meta_box );

		// Register title.
		pll_register_string( 'title', $meta_box['title'] ?? '', $context );

		// Register tab labels.
		if ( ! empty( $meta_box['tabs'] ) ) {
			foreach ( $meta_box['tabs'] as $key => $tab ) {
				pll_register_string( 'tab_' . $key, $tab['label'] ?? '', $context );
			}
		}

		$this->register_fields_strings( $meta_box['fields'], $context );

		return $meta_box;
	}

	private function register_fields_strings( array $fields, string $context, string $base_id = '' ): void {
		foreach ( $fields as $index => $field ) {
			$this->register_field_strings( $field, $context, $base_id, $index );
		}
	}

	private function register_field_strings( array $field, string $context, string $base_id = '', int $index = 0 ): void {
		$field_id = $this->get_field_id( $field, $index );
		$id       = $base_id ? $base_id . '_' . $field_id : $field_id;

		// Register field attributes.
		foreach ( $this->keys as $key => $label ) {
			pll_register_string( $id . '_' . $key, $field[ $key ] ?? '', $context );
		}

		// Register options for select/radio/checkbox fields.
		if ( in_array( $field['type'], [ 'select', 'radio', 'checkbox_list', 'select_advanced', 'button_group', 'image_select', 'autocomplete' ], true ) ) {
			$options = $field['options'] ?? [];
			foreach ( $options as $key => $option ) {
				if ( is_string( $option ) ) {
					pll_register_string( $id . '_option_' . $key, $option, $context );
				}
			}
		}

		// Register tooltip.
		if ( isset( $field['tooltip'] ) ) {
			$tooltip = '';
			if ( is_string( $field['tooltip'] ) ) {
				$tooltip = $field['tooltip'];
			} elseif ( isset( $field['tooltip']['content'] ) ) {
				$tooltip = $field['tooltip']['content'];
			}

			if ( ! empty( $tooltip ) ) {
				pll_register_string( $id . '_tooltip', $tooltip, $context );
			}
		}

		// Register nested fields for group type.
		if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
			$this->register_fields_strings( $field['fields'], $context, $id );
		}
	}

	public function use_translations( array $meta_box ): array {
		if ( ! empty( $meta_box['title'] ) ) {
			$meta_box['title'] = pll__( $meta_box['title'] );
		}

		if ( ! empty( $meta_box['tabs'] ) ) {
			foreach ( $meta_box['tabs'] as &$tab ) {
				if ( ! empty( $tab['label'] ) ) {
					$tab['label'] = pll__( $tab['label'] );
				}
			}
		}

		$this->use_fields_translations( $meta_box['fields'] );

		return $meta_box;
	}

	private function use_fields_translations( array &$fields, string $base_id = '' ): void {
		foreach ( $fields as $index => &$field ) {
			$this->use_field_translations( $field, $base_id, $index );
		}
	}

	private function use_field_translations( array &$field, string $base_id = '', int $index = 0 ): void {
		$field_id = $this->get_field_id( $field, $index );
		$id       = $base_id ? $base_id . '_' . $field_id : $field_id;

		// Translate field attributes.
		foreach ( $this->keys as $key => $label ) {
			if ( ! empty( $field[ $key ] ) ) {
				$field[ $key ] = pll__( $field[ $key ] );
			}
		}

		// Translate options for select/radio/checkbox fields.
		if ( in_array( $field['type'], [ 'select', 'radio', 'checkbox_list', 'select_advanced', 'button_group', 'image_select', 'autocomplete' ], true ) ) {
			$options = $field['options'] ?? [];
			foreach ( $options as $key => $option ) {
				if ( is_string( $option ) ) {
					$options[ $key ] = pll__( $option );
				}
			}
			$field['options'] = $options;
		}

		// Translate tooltip.
		if ( isset( $field['tooltip'] ) ) {
			$tooltip = '';
			if ( is_string( $field['tooltip'] ) ) {
				$tooltip = $field['tooltip'];
			} elseif ( isset( $field['tooltip']['content'] ) ) {
				$tooltip = $field['tooltip']['content'];
			}
			if ( ! empty( $tooltip ) ) {
				$field['tooltip'] = pll__( $tooltip );
			}
		}

		// Translate nested fields for group type.
		if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
			$this->use_fields_translations( $field['fields'], $id );
		}
	}

	private function get_field_id( array $field, int $index = 0 ): string {
		return empty( $field['id'] ) ? sanitize_key( $field['type'] ) . '_' . $index : $field['id'];
	}

	private function get_context( array $meta_box ): string {
		// translators: %s is the title of the field group.
		return sprintf( __( 'Meta Box Field Group: %s', 'meta-box-builder' ), $meta_box['title'] );
	}
}