<?php
namespace MBB\Integrations\Polylang;

class Manager {
	public function __construct() {
		// Run parser even when Polylang is not active
		// to fix fields_translations data still stored in settings and it grows after saving field groups.
		new Parser();

		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}

		new FieldGroup();
		new SettingsPage();
		new Relationship();
		new FieldGroupValues();
	}
}