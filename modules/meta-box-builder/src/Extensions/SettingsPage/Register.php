<?php
namespace MBB\Extensions\SettingsPage;

class Register {
	public function __construct() {
		$this->register_post_type();

		add_filter( 'mb_settings_pages', [ $this, 'register_settings_pages' ] );
	}

	private function register_post_type() {
		// Register main post type 'mb-settings-page'.
		$labels = [
			'name'               => _x( 'Settings Pages', 'Post Type General Name', 'meta-box-builder' ),
			'singular_name'      => _x( 'Settings Page', 'Post Type Singular Name', 'meta-box-builder' ),
			'menu_name'          => __( 'Settings Page', 'meta-box-builder' ),
			'name_admin_bar'     => __( 'Settings Page', 'meta-box-builder' ),
			'parent_item_colon'  => __( 'Parent Settings Page:', 'meta-box-builder' ),
			'all_items'          => __( 'Settings Pages', 'meta-box-builder' ),
			'add_new_item'       => __( 'Add New', 'meta-box-builder' ),
			'add_new'            => __( 'New Settings Page', 'meta-box-builder' ),
			'new_item'           => __( 'New Settings Page', 'meta-box-builder' ),
			'edit_item'          => __( 'Edit Settings Page', 'meta-box-builder' ),
			'update_item'        => __( 'Update Settings Page', 'meta-box-builder' ),
			'view_item'          => __( 'View Settings Page', 'meta-box-builder' ),
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
				'edit_post'              => 'edit_mb_settings_page',
				'read_post'              => 'read_mb_settings_page',
				'delete_post'            => 'delete_mb_settings_page',

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

		register_post_type( 'mb-settings-page', $args );
	}
	// phpcs:enable

	public function register_settings_pages( $settings_pages ) {
		$query = new \WP_Query( [
			'posts_per_page'         => -1,
			'post_status'            => 'publish',
			'post_type'              => 'mb-settings-page',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		] );

		foreach ( $query->posts as $post ) {
			$settings_page = get_post_meta( $post->ID, 'settings_page', true );
			if ( empty( $settings_page ) || ! is_array( $settings_page ) ) {
				continue;
			}

			// Allow WPML to modify the settings page to use translations.
			$settings_page = apply_filters( 'mbb_settings_page', $settings_page, $post );

			$settings_pages[] = $settings_page;
		}

		return $settings_pages;
	}
}
