<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;

class IncludeExclude {
	public function __construct() {
		if ( ! Data::is_extension_active( 'meta-box-include-exclude' ) ) {
			return;
		}
		add_filter( 'mbb_settings_controls', [ $this, 'add_settings_controls' ] );
	}

	public function add_settings_controls( $controls ) {
		$controls[2] = Control::IncludeExclude( 'include_exclude' );

		return $controls;
	}
}
