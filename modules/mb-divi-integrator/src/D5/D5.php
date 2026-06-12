<?php

namespace MBDI\D5;

use MBDI\D5\Modules\FieldModule;
use MBDI\D5\Modules\CloneableModule;

class D5 {

	public static function init(): void {
		if ( ! defined( 'RWMB_VER' ) ) {
			return;
		}

		add_action( 'divi_module_library_modules_dependency_tree', [ self::class, 'register_dependency_tree' ] );
	}

	public static function register_dependency_tree( $dependency_tree ): void {
		if ( ! et_builder_d5_enabled() ) {
			return;
		}

		$dependency_tree->add_dependency( new FieldModule() );
		$dependency_tree->add_dependency( new CloneableModule() );
	}
}
