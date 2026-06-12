<?php

namespace MBDI\D5\Modules;

class CloneableModule implements \ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface {

	public function load() {
		$json_path = MBDI_PATH . 'modules-json/cloneable/';

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
		global $wp_query;

		$cloneable_field = $attrs['field']['innerContent']['desktop']['value'] ?? '';
		$layout_id       = $attrs['layout']['innerContent']['desktop']['value'] ?? '';

		if ( ! $cloneable_field || ! $layout_id ) {
			return '';
		}

		$post_id   = $wp_query->get_queried_object_id();
		$post_type = get_post_type( $post_id );

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
		$field          = $field_registry->get( $cloneable_field, $sub_type, $object_type );

		if ( ! $field || ! ( $field['clone'] ?? false ) ) {
			return '';
		}

		$groups = rwmb_meta( $cloneable_field, $args, $identifier );

		if ( empty( $groups ) || ! is_array( $groups ) ) {
			return '';
		}

		$layout_post = get_post( $layout_id );

		if ( ! $layout_post ) {
			return '';
		}

		$layout_content = $layout_post->post_content;
		$output         = '';

		foreach ( $groups as $index => $group ) {
			$append  = ' index="' . esc_attr( $index ) . '" array="' . esc_attr( $cloneable_field ) . '"';
			$scoped  = str_replace( '[mbdi_field', "[mbdi_field {$append}", $layout_content );
			$output .= $scoped;
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
				'children'            => do_shortcode( $output ),
			]
		);
	}
}
