<?php
namespace MBB\Integrations\WPML;

class Manager {
	public function __construct() {
		if ( ! $this->is_active() ) {
			return;
		}

		new SettingsPage();
		new FieldGroup();
		new Relationship();
	}

	private function is_active(): bool {
		return defined( 'ICL_SITEPRESS_VERSION' );
	}
}
