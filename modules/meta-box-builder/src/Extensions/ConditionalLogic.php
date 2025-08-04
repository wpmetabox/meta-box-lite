<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;

class ConditionalLogic {
	public function __construct() {
		if ( ! Data::is_extension_active( 'meta-box-conditional-logic' ) ) {
			return;
		}

		add_filter( 'mbb_field_controls', [ $this, 'add_field_controls' ], 10, 2 );
	}

	public function add_field_controls( array $controls, string $type ): array {
		if ( in_array( $type, [ 'hidden', 'tab' ] ) ) {
			return $controls;
		}
		$controls[] = Control::ConditionalLogic( 'conditional_logic', [], [], 'conditional_logic' );

		return $controls;
	}
}
