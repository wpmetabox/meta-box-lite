<?php
namespace MBB\Extensions\SettingsPage;

class Manager {
	public function __construct() {
		new Register();
		new Generator();
		new Save();

		if ( is_admin() ) {
			new Edit( 'mb-settings-page' );
		}
	}
}
