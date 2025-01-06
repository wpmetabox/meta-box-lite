<?php
namespace MBB\Extensions;

use MBB\Helpers\Data;

class Relationships {
	public function __construct() {
		if ( ! Data::is_extension_active( 'mb-relationships' ) ) {
			return;
		}

		new \MBB\Relationships\Register();
		new \MBB\Relationships\RestApi();
		new \MBB\Relationships\Generator();

		if ( is_admin() ) {
			new \MBB\Relationships\Edit( 'mb-relationship', __( 'Relationship ID', 'meta-box-builder' ) );
			new \MBB\Relationships\Delete();
		}
	}
}
