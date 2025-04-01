<?php
namespace MBB\RestApi;

use WP_REST_Request;

class ShowHide extends Base {
	public function show_hide( WP_REST_Request $request ) {
		$name = $request->get_param( 'name' );
		$s    = strtolower( $request->get_param( 's' ) );

		$method = $this->get_method( $name );
		return $this->$method( $s, $name );
	}

	private function get_method( $name ): string {
		$methods = [
			'template' => 'get_templates',
			'format'   => 'get_formats',
		];
		$method  = $methods[ $name ] ?? 'get_terms';

		return $method;
	}
}
