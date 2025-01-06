<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;

class Group {
	public function __construct() {
		if ( ! Data::is_extension_active( 'meta-box-group' ) ) {
			return;
		}
		add_filter( 'mbb_field_types', [ $this, 'add_field_type' ] );
	}

	public function add_field_type( $field_types ) {
		$field_types['group'] = [
			'title'    => __( 'Group', 'meta-box-builder' ),
			'category' => 'layout',
			'controls' => [
				'name',
				'id',
				'type',
				'label_description',
				'desc',
				Control::Checkbox( 'collapsible', __( 'Collapsible', 'meta-box-builder' ) ),
				Control::Select( 'default_state', [
					'label'      => __( 'Default state', 'meta-box-builder' ),
					'dependency' => 'collapsible:true',
					'options'    => [
						'expanded'  => __( 'Expanded', 'meta-box-builder' ),
						'collapsed' => __( 'Collapsed', 'meta-box-builder' ),
					],
				], 'expanded' ),
				Control::Checkbox( 'save_state', [
					'label'      => __( 'Save state', 'meta-box-builder' ),
					'dependency' => 'collapsible:true',
				] ),
				Control::GroupTitle( 'group_title', [
					'label'      => __( 'Group title', 'meta-box-builder' ),
					'tooltip'    => __( 'Use {field_id} for a sub-field value and {#} for the clone index (if the group is cloneable)', 'meta-box-builder' ),
					'dependency' => 'collapsible:true',
				] ),
				'clone',
				'sort_clone',
				'clone_default',
				'clone_as_multiple',
				'clone_empty_start',
				'min_clone',
				'max_clone',
				'add_button',
				'before',
				'after',
				'class',
				'save_field',
				'sanitize_callback',
				'attributes',
				'custom_settings',
			],
		];

		return $field_types;
	}
}
