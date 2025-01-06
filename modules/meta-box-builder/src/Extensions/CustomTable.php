<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;

class CustomTable {
	public function __construct() {
		if ( ! Data::is_extension_active( 'mb-custom-table' ) ) {
			return;
		}
		add_filter( 'mbb_settings_controls', [ $this, 'add_settings_controls' ] );
	}

	public function add_settings_controls( $controls ) {
		$controls[16] = Control::CustomTable( 'custom_table' );

		return $controls;
	}
}
