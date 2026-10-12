<?php
namespace MBB\Extensions\Relationships;

use MBB\JsonService;
use MBB\LocalJson;
use MB_Relationships_API;
use WP_Query;

class Register {
	public function __construct() {
		$this->register_post_type();

		add_action( 'mb_relationships_init', [ $this, 'register_relationships' ] );
	}

	private function register_post_type() {
		$labels = [
			'name'               => _x( 'Relationships', 'Post Type General Name', 'meta-box-builder' ),
			'singular_name'      => _x( 'Relationship', 'Post Type Singular Name', 'meta-box-builder' ),
			'menu_name'          => __( 'Relationship', 'meta-box-builder' ),
			'name_admin_bar'     => __( 'Relationship', 'meta-box-builder' ),
			'parent_item_colon'  => __( 'Parent Relationship:', 'meta-box-builder' ),
			'all_items'          => __( 'Relationships', 'meta-box-builder' ),
			'add_new_item'       => __( 'Add New', 'meta-box-builder' ),
			'add_new'            => __( 'New Relationship', 'meta-box-builder' ),
			'new_item'           => __( 'New Relationship', 'meta-box-builder' ),
			'edit_item'          => __( 'Edit Relationship', 'meta-box-builder' ),
			'update_item'        => __( 'Update Relationship', 'meta-box-builder' ),
			'view_item'          => __( 'View Relationship', 'meta-box-builder' ),
			'search_items'       => __( 'Search', 'meta-box-builder' ),
			'not_found'          => __( 'Not found', 'meta-box-builder' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'meta-box-builder' ),
		];

		$args = [
			'labels'       => $labels,
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'meta-box',
			'rewrite'      => false,
			'supports'     => [ 'title' ],
			'map_meta_cap' => true,
			'capabilities' => [
				// Meta capabilities.
				'edit_post'              => 'edit_mb_relationship',
				'read_post'              => 'read_mb_relationship',
				'delete_post'            => 'delete_mb_relationship',

				// Primitive capabilities used outside of map_meta_cap():
				'edit_posts'             => 'manage_options',
				'edit_others_posts'      => 'manage_options',
				'publish_posts'          => 'manage_options',
				'read_private_posts'     => 'manage_options',

				// Primitive capabilities used within map_meta_cap():
				'read'                   => 'read',
				'delete_posts'           => 'manage_options',
				'delete_private_posts'   => 'manage_options',
				'delete_published_posts' => 'manage_options',
				'delete_others_posts'    => 'manage_options',
				'edit_private_posts'     => 'manage_options',
				'edit_published_posts'   => 'manage_options',
				'create_posts'           => 'manage_options',
			],
		];

		register_post_type( 'mb-relationship', $args );
	}

	public function register_relationships(): void {
		$relationships = LocalJson::is_enabled()
			? $this->get_json_relationships()
			: $this->get_database_relationships();

		foreach ( $relationships as $relationship ) {
			MB_Relationships_API::register( $relationship );
		}
	}

	private function get_database_relationships(): array {
		$relationships = [];

		$query = new WP_Query( [
			'posts_per_page'         => -1,
			'post_status'            => 'publish',
			'post_type'              => 'mb-relationship',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		] );

		foreach ( $query->posts as $post ) {
			$relationship = get_post_meta( $post->ID, 'relationship', true );
			if ( empty( $relationship ) || ! is_array( $relationship ) ) {
				continue;
			}

			// Allow WPML to translate relationship data.
			$relationships[] = apply_filters( 'mbb_relationship', $relationship, $post );
		}

		return $relationships;
	}

	private function get_json_relationships(): array {
		$relationships = [];

		foreach ( JsonService::get_unparsed( 'mb-relationship' ) as $item ) {
			$data         = $item['data'];
			$relationship = $data['relationship'] ?? [];
			if ( empty( $relationship ) || ! is_array( $relationship ) ) {
				continue;
			}

			$post_object = (object) [
				'post_title' => $data['post_title'] ?? '',
				'post_name'  => $data['post_name'] ?? '',
			];

			$relationships[] = apply_filters( 'mbb_relationship', $relationship, $post_object );
		}

		return $relationships;
	}
}
