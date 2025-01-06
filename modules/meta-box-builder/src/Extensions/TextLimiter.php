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
		if ( $type === 'tab' ) {
			return $controls;
		}

		$control = Control::TextLimiter( 'text_limiter', [
			'label'       => __( 'Text limit', 'meta-box-builder' ),
			// Translators: %s - Link to docs.
			'description' => sprintf( __( 'Limit the content by characters or words. Leave empty or enter 0 for no limit. <a href="%s" target="_blank">Learn more</a>.', 'meta-box-builder' ), 'https://docs.metabox.io/extensions/meta-box-text-limiter/"' ),
		], [], 'general' );

		return Control::insert( $controls, 'std', $control );
	}
}
