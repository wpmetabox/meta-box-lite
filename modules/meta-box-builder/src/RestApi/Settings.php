<?php
namespace MBB\RestApi;

use MBB\Control;

class Settings extends Base {
	public function get_settings_controls() {
		// Use big numeric index for extensions to add at specific places.
		$controls = [
			0  => Control::Location( 'location' ),
			10 => Control::Post( 'post' ),
			20 => Control::Input( 'class', [
				'label'   => __( 'Custom CSS class', 'meta-box-builder' ),
				'tooltip' => __( 'Custom CSS class for the meta box wrapper', 'meta-box-builder' ),
			] ),
			30 => Control::Input( 'prefix', [
				'label'   => __( 'Field ID prefix', 'meta-box-builder' ),
				'tooltip' => __( 'Auto add a prefix to all field IDs to keep them separated from other field groups or other plugins. Leave empty to ignore this or use underscore (_) to make the fields hidden.', 'meta-box-builder' ),
			] ),
			40 => Control::KeyValue( 'custom_settings', [
				'label'   => __( 'Custom settings', 'meta-box-builder' ),
				'tooltip' => __( 'Apply to the current field group. For individual fields, please go to each field > tab Advanced.', 'meta-box-builder' ),
			] ),
		];

		$controls = apply_filters( 'mbb_settings_controls', $controls );

		ksort( $controls, SORT_NUMERIC );

		return array_values( $controls );
	}
}
