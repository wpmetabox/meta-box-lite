<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use MBDI\Extension;

class MBDI_Clonable extends ET_Builder_Module {


	public $slug       = 'mbdi_cloneable';
	public $vb_support = 'on';

	protected $module_credits = [
		'module_uri' => 'https://metabox.io/plugins/mb-divi-integrator',
		'author'     => '',
		'author_uri' => '',
	];

	public function init() {
		$this->name = esc_html__( 'Meta Box Cloneable', 'mbdi' );
	}

	public function get_fields() {
		// Get all divi layouts.
		$fields  = Extension::get_fields();
		$options = $fields['layout_options'];
		$fields  = $fields['cloneable_field_options'];

		return [
			'layout' => [
				'label'           => esc_html__( 'Layout', 'mbdi' ),
				'type'            => 'select',
				'options'         => $options,
				'option_category' => 'configuration',
				'description'     => esc_html__( 'Select a layout to display.', 'mbdi' ),
				'toggle_slug'     => 'main_content',
			],
			'field'  => [
				'label'           => esc_html__( 'Cloneable Field', 'mbdi' ),
				'type'            => 'select',
				'options'         => $fields,
				'option_category' => 'configuration',
				'description'     => esc_html__( 'Select cloneable field which applied to that layout.', 'mbdi' ),
				'toggle_slug'     => 'main_content',
			],
		];
	}

	public function render( $attrs, $content, $render_slug ) {
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

		$cloneable_field = $this->props['field'];

		if ( ! $cloneable_field ) {
			return;
		}

		$field_registry = rwmb_get_registry( 'field' );
		// Check if the field is cloneable.
		$field = $field_registry->get( $cloneable_field, $sub_type, $object_type );

		if ( ! $field || ! $field['clone'] ) {
			return;
		}

		// Get all groups.
		$groups = rwmb_meta( $cloneable_field, $args, $identifier );

		$layout = $this->props['layout'];

		if ( ! $layout || empty( $groups ) ) {
			return;
		}

		$layout = get_post( $layout );

		$content = $layout->post_content;
		$output  = '';

		// Loop through each group.
		foreach ( $groups as $index => $group ) {
			// Add index and cloneable field name to each field so children fields can know
			// which group they belong to and current loop index.
			$append  = ' index="' . $index . '" array="' . $cloneable_field . '"';
			$scoped  = str_replace( '[mbdi_field', "[mbdi_field {$append}", $content );
			$output .= $scoped;
		}

		return do_shortcode( $output );
	}
}

new MBDI_Clonable;
