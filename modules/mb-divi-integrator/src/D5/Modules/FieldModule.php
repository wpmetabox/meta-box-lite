<?php

namespace MBDI\D5\Modules;

use MBDI\Output;

class FieldModule implements \ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface {

	public function load() {
		$json_path = MBDI_PATH . 'modules-json/field/';

		add_action(
			'init',
			function () use ( $json_path ) {
				\ET\Builder\Packages\ModuleLibrary\ModuleRegistration::register_module(
					$json_path,
					[
						'render_callback' => [ self::class, 'render_callback' ],
					]
				);
			}
		);
	}

	public static function render_callback( $attrs, $content, $block, $elements ) {
		$field_id = $attrs['metaboxFieldId']['innerContent']['desktop']['value'] ?? '';

		if ( empty( $field_id ) ) {
			return '';
		}

		global $wp_query;

		$post_id = $wp_query->get_queried_object_id();

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

		$index = $attrs['_index'] ?? null;
		$array = $attrs['_array'] ?? null;

		$field_registry = rwmb_get_registry( 'field' );

		if ( false !== strpos( $field_id, '.' ) ) {
			$group_key  = explode( '.', $field_id )[0];
			$nested_key = explode( '.', $field_id )[1];

			$field           = $field_registry->get( $nested_key, $sub_type, $object_type );
			$group_field     = $field_registry->get( $group_key, $sub_type, $object_type );
			$group_cloneable = $group_field['clone'] ?? false;

			$group_value = rwmb_meta( $group_key, $args, $identifier );

			if ( ! is_array( $group_value ) ) {
				return '';
			}

			if ( $group_cloneable ) {
				$group_value = $group_value[ (int) $index ] ?? [];
			}

			$field_value = $group_value[ $nested_key ] ?? '';
		} else {
			$field_value = rwmb_meta( $field_id, $args, $identifier );
			$field       = $field_registry->get( $field_id, $sub_type, $object_type );
		}

		if ( is_numeric( $index ) && $array ) {
			$array_field = $field_registry->get( $array, $sub_type, $object_type );
			if ( $array_field['type'] === 'group' && false !== strpos( $field_id, '.' ) ) {
				$group_key  = explode( '.', $field_id )[0];
				$nested_key = explode( '.', $field_id )[1];

				$group_value = rwmb_meta( $group_key, $args, $identifier );
				if ( is_array( $group_value ) ) {
					$group_value = $group_value[ (int) $index ] ?? [];
				}
				$field_value = $group_value[ $nested_key ] ?? '';
				foreach ( $array_field['fields'] as $f ) {
					if ( $f['id'] === $nested_key ) {
						$field = $f;
						break;
					}
				}
			} else {
				$field_value = rwmb_meta( $field_id, $args, $identifier );
				if ( is_array( $field_value ) && isset( $field_value[ (int) $index ] ) ) {
					$field_value = $field_value[ (int) $index ];
				}
				$field = $field_registry->get( $array, $sub_type, $object_type );
			}
		}

		if ( ! $field ) {
			return '';
		}

		$field_value = Output::from([
			'value' => $field_value,
			'field' => $field,
			'attrs' => [],
			'raw'   => false,
		]);

		if ( is_array( $field_value ) || is_object( $field_value ) ) {
			$field_value = '';
		}

		return \ET\Builder\Packages\Module\Module::render(
			[
				'id'                  => $block->parsed_block['id'],
				'name'                => $block->block_type->name,
				'moduleCategory'      => $block->block_type->category,
				'attrs'               => $attrs,
				'elements'            => $elements,
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],
				'parentAttrs'         => [],
				'parentId'            => '',
				'parentName'          => '',
				'classnamesFunction'  => null,
				'stylesComponent'     => null,
				'scriptDataComponent' => null,
				'children'            => (string) $field_value,
			]
		);
	}
}
