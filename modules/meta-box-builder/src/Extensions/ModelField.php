<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;

/**
 * Register the model field type in the field group builder.
 * Always listed; disabled (gray + tooltip) when MB Custom Table is inactive.
 */
class ModelField {
	public function __construct() {
		add_filter( 'mbb_field_types', [ $this, 'add_field_type' ] );
	}

	public function add_field_type( array $field_types ): array {
		$field_types['model'] = [
			'title'          => __( 'Custom Model', 'meta-box-builder' ),
			'category'       => 'wordpress',
			'disabled'       => ! Data::is_extension_active( 'mb-custom-table' ),
			'disabled_text'  => __( 'This field type requires the MB Custom Table extension.', 'meta-box-builder' ),
			'disabled_label' => __( 'MB Custom Table', 'meta-box-builder' ),
			'description'    => __( 'For selecting items from a custom model', 'meta-box-builder' ),
			'controls'       => [
				'required',
				'clone_settings',
				'type',
				'name',
				'id',
				Control::Select( 'model', [
					'label'    => __( 'Model', 'meta-box-builder' ),
					'options'  => Data::get_model_options(),
					'required' => true,
				] ),
				Control::ItemTitle( 'item_title', [
					'label'   => __( 'Item title', 'meta-box-builder' ),
					'tooltip' => __( 'Click … to insert a column, or type a template like {email} — {amount}', 'meta-box-builder' ),
				] ),
				Control::Select( 'field_type', [
					'label'   => __( 'Field type', 'meta-box-builder' ),
					'options' => [
						'select'          => __( 'Select', 'meta-box-builder' ),
						'select_advanced' => __( 'Select advanced', 'meta-box-builder' ),
						'checkbox_list'   => __( 'Checkbox list', 'meta-box-builder' ),
						'radio_list'      => __( 'Radio list', 'meta-box-builder' ),
					],
				], 'select_advanced' ),
				'add_new',
				'multiple',
				Control::KeyValue( 'query_args', [
					'label'       => __( 'Query args', 'meta-box-builder' ),
					'description' => __( 'Extra query arguments for listing model rows (limit, orderby, order, …).', 'meta-box-builder' ),
					'keys'        => [ 'limit', 'orderby', 'order', 's' ],
					'values'      => [
						'order' => [ 'ASC', 'DESC' ],
					],
				] ),
				'select_all_none',
				'label_description',
				'desc',
				'placeholder',
				'validation',
				'class',
				'before',
				'after',
				'hide_from_block_bindings',
				'save_field',
				'sanitize_callback',
				'attributes',
				'custom_settings',
			],
		];

		return $field_types;
	}
}
