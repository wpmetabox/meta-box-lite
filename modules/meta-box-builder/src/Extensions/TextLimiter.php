<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;

class TextLimiter {
	public function __construct() {
		if ( ! Data::is_extension_active( 'meta-box-text-limiter' ) ) {
			return;
		}

		add_filter( 'mbb_field_controls', [ $this, 'add_field_controls' ], 10, 2 );
	}

	public function add_field_controls( $controls, $type ) {
		if ( ! in_array( $type, [ 'text', 'textarea', 'wysiwyg' ] ) ) {
			return $controls;
		}

		$control = Control::TextLimiter( 'text_limiter', [
			'label'       => __( 'Text limit', 'meta-box-builder' ),
			'description' => __( 'Leave empty or enter 0 for no limit.', 'meta-box-builder' ),
		], [], 'general' );

		return Control::insert( $controls, 'std', $control );
	}
}
