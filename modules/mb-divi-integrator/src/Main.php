<?php

namespace MBDI;

class Main {

	public function __construct() {
		add_action( 'init', [ $this, 'init' ], 20 );

		add_action( 'divi_extensions_init', function () {
			new Extension();
		} );
	}

	public function init(): void {
		if ( ! defined( 'RWMB_VER' ) || ! defined( 'ET_BUILDER_VERSION' ) ) {
			return;
		}

		/** Render dynamic content placeholder and output */
		add_filter('et_builder_dynamic_content_meta_value', [
			$this,
			'maybe_filter_dynamic_content_meta_value',
		], 10, 3);

		/** Add dynamic content fields */
		add_filter('et_builder_custom_dynamic_content_fields', [
			$this,
			'maybe_filter_dynamic_content_fields',
		], 10, 3);
	}

	/**
	 * Format Meta Box meta values accordingly.
	 *
	 * @param string  $meta_value
	 * @param string  $meta_key
	 * @param integer $post_id
	 *
	 * @return string
	 */
	public function maybe_filter_dynamic_content_meta_value( $meta_value, $meta_key, $post_id ) {
		global $wp_query;

		$post_type   = get_post_type( $post_id );
		$object_type = 'post';
		$sub_type    = $post_type;
		$identifier  = $post_id;
		$args        = [];

		// If we're in Divi Builder, use the placeholder value.
		if ( et_theme_builder_is_layout_post_type( $post_type ) ) {
			return $this->format_placeholder_value( $meta_key, $post_id );
		}

		$is_blog_query = isset( $wp_query->et_pb_blog_query ) && $wp_query->et_pb_blog_query;

		if ( ! $is_blog_query && ( is_category() || is_tag() || is_tax() ) ) {
			$object_type = 'term';
			$term        = get_queried_object();
			$sub_type    = $term->taxonomy;
			$identifier  = $term->term_id;
			$args        = [
				'object_type' => 'term',
			];
		} elseif ( is_author() ) {
			$object_type = 'user';
			$sub_type    = 'user';
			$user        = get_queried_object();
			$identifier  = $user->ID;
			$args        = [
				'object_type' => 'user',
			];
		}

		$field_registry = rwmb_get_registry( 'field' );

		// If $meta_key contains dot (.), it's a sub-field.
		// We need to get the parent field first.
		if ( false !== strpos( $meta_key, '.' ) ) {
			$group_key  = explode( '.', $meta_key )[0];
			$nested_key = explode( '.', $meta_key )[1];

			$field           = $field_registry->get( $nested_key, $sub_type, $object_type );
			$group_field     = $field_registry->get( $group_key, $sub_type, $object_type );
			$group_cloneable = $group_field['clone'] ?? false;

			$group_value = rwmb_meta( $group_key, $args, $identifier );

			if ( ! is_array( $group_value ) ) {
				return $meta_value;
			}

			if ( $group_cloneable ) {
				$group_value = $group_value[0];
			}

			$meta_box_value = $group_value[ $nested_key ] ?? '';
		} else {
			$meta_box_value = rwmb_meta( $meta_key, $args, $identifier );
			$field          = $field_registry->get( $meta_key, $sub_type, $object_type );
		}

		if ( false === $meta_box_value ) {
			return $meta_value;
		}

		$meta_box_value = $this->format_field_value( $meta_box_value, $field );

		if ( is_array( $meta_box_value ) || is_object( $meta_box_value ) ) {
			// Avoid exposing unformatted values.
			$meta_box_value = '';
		}

		return (string) $meta_box_value;
	}

	/**
	 * Format a field value based on the field type.
	 *
	 * @param mixed $value
	 * @param array $field
	 *
	 * @return mixed
	 */
	protected function format_field_value( $value, $field ) {
		if ( ! is_array( $field ) || empty( $field['type'] ) ) {
			return $value;
		}

		return Output::from([
			'value' => $value,
			'field' => $field,
			'attrs' => [],
			'raw'   => false,
		]);
	}


	/**
	 * Format a placeholder value based on the field type.
	 *
	 * @param string  $meta_key
	 * @param integer $post_id
	 *
	 * @return mixed
	 */
	protected function format_placeholder_value( string $meta_key, int $post_id ) {
		$field_registry = rwmb_get_registry( 'field' );
		$field          = $field_registry->get( $meta_key, 'post' );

		if ( ! is_array( $field ) || empty( $field['type'] ) ) {
			return esc_html__( 'Your Meta Box Field Value Will Display Here', 'mbdi' );
		}

		$value = esc_html(
			sprintf(
				/* translators: %1$s - field label */
				__( 'Your "%1$s" Meta Box Field Value Will Display Here', 'mbdi' ),
				$field['label']
			)
		);

		switch ( $field['type'] ) {
			case 'image':
				$value = ET_BUILDER_PLACEHOLDER_LANDSCAPE_IMAGE_DATA;
				break;

			case 'taxonomy':
				$value = esc_html(
					implode(
						', ',
						[
							__( 'Category 1', 'et_builder' ),
							__( 'Category 2', 'et_builder' ),
							__( 'Category 3', 'et_builder' ),
						]
					)
				);
				break;
		}

		return $value;
	}

	public function maybe_filter_dynamic_content_fields( $custom_fields, $post_id, $raw_custom_fields ) {
		if ( ! $post_id || et_theme_builder_is_layout_post_type( get_post_type( $post_id ) ) ) {
			$post_id = 0;
		}

		return $this->maybe_filter_dynamic_content_fields_from_groups( $custom_fields, $post_id, $raw_custom_fields );
	}

	public function maybe_filter_dynamic_content_fields_from_groups( $custom_fields, $post_id, $raw_custom_fields ) {
		$meta_box_registry = rwmb_get_registry( 'meta_box' );
		$meta_boxes        = $meta_box_registry->all();

		if ( ! $meta_boxes ) {
			return $custom_fields;
		}

		foreach ( $meta_boxes as $meta_box ) {
			$meta_box = $meta_box->meta_box;

			$fields = $this->flatten( $meta_box['fields'] );

			foreach ( $fields as $field ) {
				if ( ! $this->is_field_supported( $field ) ) {
					continue;
				}

				$settings = [
					'label'    => esc_html( $field['name'] ),
					'type'     => 'any',
					'fields'   => [
						'before' => [
							'label'   => et_builder_i18n( 'Before' ),
							'type'    => 'text',
							'default' => '',
							'show_on' => 'text',
						],
						'after'  => [
							'label'   => et_builder_i18n( 'After' ),
							'type'    => 'text',
							'default' => '',
							'show_on' => 'text',
						],
					],
					'meta_key' => $field['meta_key'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- false positive, not a meta query.
					'custom'   => true,
					'group'    => 'Meta Box: ' . $meta_box['title'],
				];

				if ( current_user_can( 'unfiltered_html' ) ) {
					$settings['fields']['enable_html'] = [
						'label'   => esc_html__( 'Enable raw HTML', 'et_builder' ),
						'type'    => 'yes_no_button',
						'options' => [
							'on'  => et_builder_i18n( 'Yes' ),
							'off' => et_builder_i18n( 'No' ),
						],
						// Set enable_html default to `on` for taxonomy fields so builder
						// automatically renders taxonomy list properly as unescaped HTML.
						'default' => in_array( $field['type'], [ 'taxonomy', 'taxonomy_advanced' ], true ) ? 'on' : 'off',
						'show_on' => 'text',
					];
				}

				$custom_fields[ "custom_meta_{$field['id']}" ] = $settings;
			}
		}

		return $custom_fields;
	}

	/**
	 * Check if a field is supported by Dynamic Content.
	 *
	 * @param array $field
	 *
	 * @return bool
	 */
	protected function is_field_supported( array $field ): bool {
		return ! in_array( $field['type'], [ 'divider', 'heading', 'tab', 'group' ], true );
	}

	/**
	 * Flatten fields array.
	 * This is used to get all fields in a meta box, including fields in groups into a single dimensional array.
	 * Fields in groups will have their label prefixed with the group label. For example: "Group: Field".
	 *
	 * @param array $fields
	 * @param array $parent Prefix for field label. Used for group fields.
	 *
	 * @return array
	 */
	protected function flatten( array $fields, array $parent = [] ): array {
		$output = [];

		foreach ( $fields as $field ) {
			// Skip tab fields.
			if ( $field['type'] === 'tab' ) {
				continue;
			}

			// Add prefix to field label. Top level fields will not have a prefix.
			if ( ! in_array( $field['type'], [ 'tab', 'group' ], true ) ) {
				$field['meta_key'] = $field['id']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- false positive, not a meta query.

				if ( $parent ) {
					$field['name']     = $parent['name'] . ': ' . $field['name'];
					$field['meta_key'] = $parent['id'] . '.' . $field['id']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- false positive, not a meta query.
				}

				$output[] = $field;
			}

			if ( 'group' === $field['type'] ) {
				$children = $this->flatten( $field['fields'], $field );
				$output   = array_merge( $output, $children );
			}
		}

		return $output;
	}
}
