<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;

class RestApi {
	public function __construct() {
		if ( ! Data::is_extension_active( 'mb-rest-api' ) ) {
			return;
		}
		add_filter( 'mbb_field_controls', [ $this, 'add_field_controls' ], 10, 2 );
	}

	public function add_field_controls( array $controls, string $type ): array {
		if ( in_array( $type, [ 'button', 'custom_html', 'divider', 'heading', 'tab' ] ) ) {
			return $controls;
		}

		$control = Control::Toggle( 'hide_from_rest', [
			'name'        => 'hide_from_rest',
			'label'       => __( 'Hide from Rest API?', 'meta-box-builder' ),
			'description' => __( 'Do not show the value of this field in the Rest API responses.', 'meta-box-builder' ),
		], false, 'advanced' );

		return Control::insert_before( $controls, 'save_field', $control );
	}
}
