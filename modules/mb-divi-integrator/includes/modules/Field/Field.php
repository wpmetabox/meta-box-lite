<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBDI\Extension;
use MBDI\FieldQuery;
use MBDI\Output;

class MBDI_Field extends ET_Builder_Module {

	public $slug = 'mbdi_field';

	public $vb_support = 'on';

	protected $module_credits = [
		'module_uri' => 'https://metabox.io/plugins/mbdi',
		'author'     => '',
		'author_uri' => '',
	];

	public function init() {
		$this->name = esc_html__( 'Meta Box Field', 'mbdi' );
	}

	public function get_fields() {
		$fields  = Extension::get_fields();
		$options = $fields['field_options'];

		return [
			'metabox_field_id' => [
				'label'           => esc_html__( 'Meta Box Field', 'mbdi' ),
				'type'            => 'select',
				'options'         => $options,
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Select a field to display.', 'mbdi' ),
				'toggle_slug'     => 'main_content',
			],
			'items_per_row'    => [
				'label'           => esc_html__( 'Items Per Row', 'mbdi' ),
				'type'            => 'select',
				'options'         => [
					'1' => esc_html__( '1', 'mbdi' ),
					'2' => esc_html__( '2', 'mbdi' ),
					'3' => esc_html__( '3', 'mbdi' ),
					'4' => esc_html__( '4', 'mbdi' ),
					'5' => esc_html__( '5', 'mbdi' ),
					'6' => esc_html__( '6', 'mbdi' ),
				],
				'default'         => '1',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Select the number of items to display per row.', 'mbdi' ),
				'toggle_slug'     => 'main_content',
			],
		];
	}

	public function render( $attrs, $content, $render_slug ) {
		global $wp_query;

		$index = $attrs['index'] ?? null;
		$array = $attrs['array'] ?? null;

		$meta_key = $this->props['metabox_field_id'];

		$post_id     = get_the_ID();
		$post_type   = get_post_type( $post_id );
		$object_type = 'post';
		$sub_type    = $post_type;
		$identifier  = $post_id;
		$args        = [];

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
			$nested      = $this->get_nested_value( $meta_key, $field_registry, $sub_type, $object_type, $args, $identifier );
			$field_value = $nested['field_value'] ?? '';
			$field       = $nested['field'];
		} else {
			$field_value = rwmb_meta( $meta_key, $args, $identifier );
			$field       = $field_registry->get( $meta_key, $sub_type, $object_type );
		}

		// Cloneable field.
		if ( is_numeric( $index ) ) {
			$array_field = $field_registry->get( $array, $sub_type, $object_type );
			if ( $array_field['type'] === 'group' && false !== strpos( $meta_key, '.' ) ) {
				$nested      = $this->get_nested_value( $meta_key, $field_registry, $sub_type, $object_type, $args, $identifier, $index );
				$field_value = $nested['field_value'];
				$field       = $nested['field'];
			} else {
				$field_value = rwmb_meta( $meta_key, $args, $identifier );
				if ( is_array( $field_value ) && isset( $field_value[ $index ] ) ) {
					$field_value = $field_value[ $index ];
				}
				$field = $field_registry->get( $array, $sub_type, $object_type );
			}
		}

		if ( ! $field ) {
			return;
		}

		return Output::from([
			'value' => $field_value,
			'field' => $field,
			'attrs' => $attrs,
			'raw'   => false,
		]);
	}

	private function get_nested_value( $meta_key, $field_registry, $sub_type, $object_type, $args, $identifier, $index = 0 ) {
		$group_key  = explode( '.', $meta_key )[0];
		$nested_key = explode( '.', $meta_key )[1];

		$group_field = $field_registry->get( $group_key, $sub_type, $object_type );

		// Find the field in the group.
		foreach ( $group_field['fields'] as $f ) {
			if ( $f['id'] === $nested_key ) {
				$field = $f;
				break;
			}
		}

		$group_cloneable = $group_field['clone'] ?? false;

		$group_value = rwmb_meta( $group_key, $args, $identifier );

		if ( ! is_array( $group_value ) ) {
			$field_value = '';
		}

		if ( $group_cloneable ) {
			$group_value = $group_value[ $index ] ?? '';
		}

		$field_value = $group_value[ $nested_key ] ?? '';

		return compact( 'field_value', 'field' );
	}
}

new MBDI_Field;
