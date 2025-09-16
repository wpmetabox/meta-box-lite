<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;

class Group {
	public function __construct() {
		add_filter( 'mbb_field_types', [ $this, 'add_field_type' ] );
	}

	public function add_field_type( $field_types ) {
		$field_types['group'] = [
			'title'    => __( 'Group', 'meta-box-builder' ),
			'category' => 'layout',
			'disabled' => ! Data::is_extension_active( 'meta-box-group' ),
			'controls' => [
				'type',
				'name',
				'id',
				'label_description',
				'desc',
				Control::Toggle( 'collapsible', __( 'Collapsible', 'meta-box-builder' ) ),
				Control::ToggleGroup( 'default_state', [
					'label'      => __( 'Default state', 'meta-box-builder' ),
					'dependency' => 'collapsible:true',
					'options'    => [
						'expanded'  => __( 'Expanded', 'meta-box-builder' ),
						'collapsed' => __( 'Collapsed', 'meta-box-builder' ),
					],
				], 'expanded' ),
				Control::Toggle( 'save_state', [
					'label'      => __( 'Remember state', 'meta-box-builder' ),
					'dependency' => 'collapsible:true',
				] ),
				Control::GroupTitle( 'group_title', [
					'label'      => __( 'Group title', 'meta-box-builder' ),
					'tooltip'    => __( 'Use {field_id} for a sub-field value and {#} for the clone index (if the group is cloneable)', 'meta-box-builder' ),
					'dependency' => 'collapsible:true',
				] ),
				'clone_settings',
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
