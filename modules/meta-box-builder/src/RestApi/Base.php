<?php
namespace MBB\RestApi;

use WP_REST_Server;
use ReflectionMethod;
use RWMB_Taxonomy_Field;
use RWMB_User_Field;
use MBB\Helpers\Data;
use MetaBox\Support\Arr;

class Base {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		$methods = $this->get_public_methods();
		$methods = array_diff( $methods, [ '__construct', 'register_routes', 'has_permission' ] );
		array_walk( $methods, [ $this, 'register_route' ] );
	}

	private function register_route( $method ) {
		$route = str_replace( [ 'get_', '_' ], [ '', '-' ], $method );
		register_rest_route( 'mbb', $route, [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, $method ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function has_permission() {
		return current_user_can( 'manage_options' );
	}

	private function get_public_methods() {
		$methods = get_class_methods( $this );
		return array_filter( $methods, function ( $method ) {
			$reflect = new ReflectionMethod( $this, $method );
			return $reflect->isPublic();
		} );
	}

	protected function get_posts( $s, $name = '', $post_types = '' ): array {
		$post_types = Arr::from_csv( $post_types );

		global $wpdb;
		$sql   = "SELECT ID, post_title FROM $wpdb->posts WHERE post_type IN ('" . implode( "','", $post_types ) . "') AND post_title LIKE '%%" . esc_sql( $s ) . "%%' ORDER BY post_title ASC LIMIT 10";
		$posts = $wpdb->get_results( $sql );

		$options = [];
		foreach ( $posts as $post ) {
			$options[] = [
				'value' => $post->ID,
				'label' => $post->post_title,
			];
		}

		return $options;
	}

	protected function get_terms( $s, $taxonomy ) {
		$field = [
			'id'         => 'mbb_api_term',
			'type'       => 'taxonomy',
			'clone'      => false,
			'query_args' => [
				'taxonomy'   => $taxonomy,
				'name__like' => $s,
				'orderby'    => 'name',
				'number'     => 10,
			],
		];

		$data = RWMB_Taxonomy_Field::query( null, $field );
		return array_values( $data );
	}

	protected function get_users( $s ) {
		$field = [
			'id'            => 'mbb_api_user',
			'type'          => 'user',
			'clone'         => false,
			'display_field' => 'display_name',
			'query_args'    => [
				'search'  => "*{$s}*",
				'number'  => 10,
				'orderby' => 'display_name',
				'order'   => 'ASC',
			],
		];

		$data = RWMB_User_Field::query( null, $field );
		return array_values( $data );
	}

	protected function get_user_roles( $s ) {
		global $wp_roles;

		$roles = $wp_roles->roles;
		$data  = [];
		foreach ( $roles as $key => $role ) {
			if ( empty( $s ) || false !== strpos( $role['name'], $s ) ) {
				$data[] = [
					'value' => $key,
					'label' => $role['name'],
				];
			}
		}
		return $data;
	}

	protected function get_templates( $s ) {
		$templates = Data::get_templates();

		// Group templates by file, which eliminates duplicates templates for multiple post types.
		$items = [];
		foreach ( $templates as $template ) {
			if ( empty( $s ) || false !== strpos( strtolower( $template['name'] ), $s ) ) {
				$items[ $template['file'] ] = $template['name'];
			}
		}

		$data = [];
		foreach ( $items as $id => $name ) {
			$data[] = [
				'value' => $id,
				'label' => $name,
			];
		}
		return $data;
	}

	protected function get_formats( $s ) {
		$items = Data::get_post_formats();
		$data  = [];
		foreach ( $items as $name ) {
			if ( empty( $s ) || false !== strpos( $name, $s ) ) {
				$data[] = [
					'value' => $name,
					'label' => ucfirst( $name ),
				];
			}
		}
		return $data;
	}
}
