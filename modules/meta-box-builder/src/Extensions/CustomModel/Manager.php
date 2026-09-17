<?php
namespace MBB\Extensions\CustomModel;

class Manager {
	public function __construct() {
		new Register();
		new Save();
		new ListTableColumns();

		if ( is_admin() ) {
			new Edit( 'mb-model' );
		}
	}
}
