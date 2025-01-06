<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;

class FrontendSubmission {
	public function __construct() {
		if ( ! Data::is_extension_active( 'mb-frontend-submission' ) ) {
			return;
		}
		add_filter( 'mbb_field_controls', [ $this, 'add_field_controls' ] );
	}

	public function add_field_controls( $controls ) {
		$controls[] = Control::Checkbox( 'hide_from_front', [
			'name'  => 'hide_from_front',
			'label' => '<a href="https://metabox.io/plugins/mb-frontend-submission/" target="_blank" rel="nofollow noopenner">' . __( 'Hide from front end?', 'meta-box-builder' ) . '</a>',
		] );

		return $controls;
	}
}
