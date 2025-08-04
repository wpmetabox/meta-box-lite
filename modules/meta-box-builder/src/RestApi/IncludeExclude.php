<?php
namespace MBB\RestApi;

use MetaBox\Support\Arr;
use WP_REST_Request;

class IncludeExclude extends Base {
	public function include_exclude( WP_REST_Request $request ) {
		$name       = $request->get_param( 'name' );
		$s          = strtolower( $request->get_param( 's' ) );
		$post_types = $request->get_param( 'post_types' ) ?: '';
		if ( is_string( $post_types ) ) {
			$post_types = Arr::from_csv( $post_types );
		}

		$method = $this->get_method( $name );
		$name   = 'get_terms' === $method ? str_replace( 'parent_', '', $name ) : $name;
		return $this->$method( $s, $name, $post_types );
	}

	private function get_method( $name ) {
		$methods = [
			'ID'               => 'get_posts',
			'parent'           => 'get_posts',
			'template'         => 'get_templates',
			'user_role'        => 'get_user_roles',
			'user_id'          => 'get_users',
			'edited_user_role' => 'get_user_roles',
			'edited_user_id'   => 'get_users',
		];
		$method  = isset( $methods[ $name ] ) ? $methods[ $name ] : 'get_terms';
		return $method;
	}
}
