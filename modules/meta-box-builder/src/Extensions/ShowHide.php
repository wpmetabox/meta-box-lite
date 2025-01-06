<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;

class ShowHide {
	public function __construct() {
		if ( ! Data::is_extension_active( 'meta-box-show-hide' ) ) {
			return;
		}
		add_filter( 'mbb_settings_controls', [ $this, 'add_settings_controls' ] );
	}

	public function add_settings_controls( $controls ) {
		$controls[4] = Control::ShowHide( 'show_hide' );

		return $controls;
	}
}
