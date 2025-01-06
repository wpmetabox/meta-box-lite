<?php
namespace MBB\Relationships;

use MBB\RestApi\Base;
use MBB\Control;
use MBB\Helpers\Data;

class RestApi extends Base {
	public function get_relationships_sides() {
		$sides = [
			[
				'title'    => __( 'From', 'meta-box-builder' ),
				'id'       => 'from',
				'controls' => $this->get_side_controls(),
			],
			[
				'title'    => __( 'To', 'meta-box-builder' ),
				'id'       => 'to',
				'controls' => $this->get_side_controls(),
			],
		];

		return $sides;
	}

	private function get_side_controls() {
		$controls = [
			// General.
			Control::Select( 'object_type', [
				'label'   => __( 'Object type', 'meta-box-builder' ),
				'options' => $this->get_object_type_options(),
			], 'post' ),
			Control::Select( 'post_type', [
				'label'      => __( 'Post type', 'meta-box-builder' ),
				'options'    => $this->get_post_types(),
				'dependency' => 'object_type:post',
			], 'post' ),
			Control::Select( 'taxonomy', [
				'label'      => __( 'Taxonomy', 'meta-box-builder' ),
				'options'    => $this->get_taxonomies(),
				'dependency' => 'object_type:term',
			], 'category' ),
			Control::Input( 'empty_message', [
				'label'   => __( 'Empty message', 'meta-box-builder' ),
				'tooltip' => __( 'The message displayed when there’s no connections', 'meta-box-builder' ),
			] ),
			// Admin filter.
			Control::Checkbox( 'admin_filter', [
				'name'       => 'admin_filter',
				'label'      => __( 'Show admin filter', 'meta-box-builder' ),
				'tooltip'    => __( 'Show a dropdown filter by this relationship in the admin table list.', 'meta-box-builder' ),
				'dependency' => 'object_type:post',
			] ),
			// Admin columns.
			Control::Checkbox( 'admin_column_enable', [
				'name'    => 'admin_column[enable]',
				'label'   => __( 'Show as an admin column', 'meta-box-builder' ),
				'tooltip' => __( 'Show this connection as a column in the All posts/terms/users table list in the admin area', 'meta-box-builder' ),
			] ),
			Control::AdminColumnsPosition( 'admin_column_position', [
				'name'       => 'admin_column[position]',
				'className'  => 'og-admin-columns-position',
				'label'      => __( 'Column position', 'meta-box-builder' ),
				'tooltip'    => __( 'Specify where to show the column in the table', 'meta-box-builder' ),
				'dependency' => 'admin_column_enable:true',
			] ),
			Control::Input( 'admin_column_title', [
				'name'       => 'admin_column[title]',
				'label'      => __( 'Column title', 'meta-box-builder' ),
				'tooltip'    => __( 'Leave empty to use the meta box title', 'meta-box-builder' ),
				'dependency' => 'admin_column_enable:true',
			] ),
			Control::Select( 'admin_column_link', [
				'name'       => 'admin_column[link]',
				'label'      => __( 'Item link type', 'meta-box-builder' ),
				'tooltip'    => __( 'The link for the items displayed in the admin column', 'meta-box-builder' ),
				'options'    => [
					'view'  => __( 'View', 'meta-box-builder' ),
					'edit'  => __( 'Edit', 'meta-box-builder' ),
					'false' => __( 'No link', 'meta-box-builder' ),
				],
				'dependency' => 'admin_column_enable:true',
			], 'view' ),

			// Meta box settings.
			Control::Input( 'meta_box_title', [
				'name'  => 'meta_box[title]',
				'label' => __( 'Title', 'meta-box-builder' ),
			], '', 'meta_box' ),
			Control::Select( 'meta_box_context', [
				'name'    => 'meta_box[context]',
				'label'   => __( 'Context', 'meta-box-builder' ),
				'options' => [
					'normal' => __( 'After content', 'meta-box-builder' ),
					'side'   => __( 'Side', 'meta-box-builder' ),
				],
			], 'side', 'meta_box' ),
			Control::Select( 'meta_box_priority', [
				'name'    => 'meta_box[priority]',
				'label'   => __( 'Priority', 'meta-box-builder' ),
				'options' => [
					'low'  => __( 'Low', 'meta-box-builder' ),
					'high' => __( 'High', 'meta-box-builder' ),
				],
			], 'low', 'meta_box' ),
			Control::Select( 'meta_box_style', [
				'name'    => 'meta_box[style]',
				'label'   => __( 'Style', 'meta-box-builder' ),
				'options' => [
					'default'  => __( 'Default', 'meta-box-builder' ),
					'seamless' => __( 'Seamless', 'meta-box-builder' ),
				],
			], 'default', 'meta_box' ),
			Control::Checkbox( 'meta_box_closed', [
				'name'  => 'meta_box[closed]',
				'label' => __( 'Collapsed by default', 'meta-box-builder' ),
			], false, 'meta_box' ),
			Control::Input( 'meta_box_class', [
				'name'  => 'meta_box[class]',
				'label' => __( 'Custom CSS class', 'meta-box-builder' ),
			], '', 'meta_box' ),

			// Field settings.
			Control::Input( 'field_name', [
				'name'  => 'field[name]',
				'label' => __( 'Label', 'meta-box-builder' ),
			], '', 'field' ),
			Control::Input( 'field_label_description', [
				'name'    => 'field[label_description]',
				'label'   => __( 'Label description', 'meta-box-builder' ),
				'tooltip' => __( 'Display below the field label', 'meta-box-builder' ),
			], '', 'field' ),
			Control::Input( 'field_desc', [
				'name'    => 'field[desc]',
				'label'   => __( 'Input description', 'meta-box-builder' ),
				'tooltip' => __( 'Display below the field input', 'meta-box-builder' ),
			], '', 'field' ),
			Control::Input( 'field_placeholder', [
				'name'  => 'field[placeholder]',
				'label' => __( 'Placeholder', 'meta-box-builder' ),
			], '', 'field' ),
			Control::Checkbox( 'add_new', [
				'name'  => 'field[add_new]',
				'label' => __( 'Add new', 'meta-box-builder' ),
			], false, 'field' ),
			Control::KeyValue( 'field_query_args', [
				'name'    => 'field[query_args]',
				'label'   => '<a href="https://developer.wordpress.org/reference/classes/wp_query/" target="_blank" rel="nofollow noopenner">' . __( 'Query args', 'meta-box-builder' ) . '</a>',
				'tooltip' => __( 'Query arguments for getting posts. Same as in the WP_Query class.', 'meta-box-builder' ),
			], [], 'field' ),
			Control::Input( 'field_max_clone', [
				'name'    => 'field[max_clone]',
				'type'    => 'number',
				'label'   => __( 'Max items', 'meta-box-builder' ),
				'tooltip' => __( 'Set to 1 to set 1-n relationship or 1-1 relationship. Leave empty for unlimited items, e.g. n-n relationship.', 'meta-box-builder' ),
			], '', 'field' ),
			Control::Input( 'field_add_button', [
				'name'    => 'field[add_button]',
				'label'   => __( 'Add more text', 'meta-box-builder' ),
				'tooltip' => __( 'Custom text for the the "+ Add more" button. Leave empty to use the default text.', 'meta-box-builder' ),
			], '', 'field' ),
			Control::Textarea( 'field_before', [
				'name'    => 'field[before]',
				'label'   => __( 'Before', 'meta-box-builder' ),
				'tooltip' => __( 'Custom HTML displayed before the field output', 'meta-box-builder' ),
			], '', 'field' ),
			Control::Textarea( 'field_after', [
				'name'    => 'field[after]',
				'label'   => __( 'After', 'meta-box-builder' ),
				'tooltip' => __( 'Custom HTML displayed after the field output', 'meta-box-builder' ),
			], '', 'field' ),
			Control::Input( 'field_class', [
				'name'  => 'field[class]',
				'label' => __( 'Custom CSS class', 'meta-box-builder' ),
			], '', 'field' ),
		];

		return $controls;
	}

	private function get_post_types() {
		$post_types = Data::get_post_types();
		$options    = [];
		foreach ( $post_types as $post_type ) {
			$options[ $post_type['slug'] ] = sprintf( '%s (%s)', $post_type['name'], $post_type['slug'] );
		}
		return $options;
	}

	private function get_taxonomies() {
		$taxonomies = Data::get_taxonomies();
		$options    = [];
		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy['slug'] ] = sprintf( '%s (%s)', $taxonomy['name'], $taxonomy['slug'] );
		}
		return $options;
	}

	private function get_object_type_options(): array {
		$options         = [];
		$options['post'] = __( 'Post', 'meta-box-builder' );
		if ( Data::is_extension_active( 'mb-term-meta' ) ) {
			$options['term'] = __( 'Term', 'meta-box-builder' );
		}
		if ( Data::is_extension_active( 'mb-user-meta' ) ) {
			$options['user'] = __( 'User', 'meta-box-builder' );
		}
		return $options;
	}
}
