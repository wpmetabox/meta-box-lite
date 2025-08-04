<?php
namespace MBB\Extensions\Relationships;

class Manager {
	public function __construct() {
		new Register();
		new Generator();
		new Save();

		if ( is_admin() ) {
			new Edit( 'mb-relationship' );
			new Delete();
		}
	}
}
