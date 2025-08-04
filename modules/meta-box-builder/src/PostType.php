<?php
namespace MBB;

class PostType {
	public function __construct() {
		$this->register_post_type();
		add_filter( 'post_updated_messages', [ $this, 'updated_messages' ] );
	}

	private function register_post_type() {
		$labels = [
			'name'               => _x( 'Field Groups', 'post type general name', 'meta-box-builder' ),
			'singular_name'      => _x( 'Field Group', 'post type singular name', 'meta-box-builder' ),
			'menu_name'          => _x( 'Custom Fields', 'admin menu', 'meta-box-builder' ),
			'name_admin_bar'     => _x( 'Custom Fields', 'add new on admin bar', 'meta-box-builder' ),
			'add_new'            => _x( 'Add New', 'meta-box-builder', 'meta-box-builder' ),
			'add_new_item'       => __( 'Add New', 'meta-box-builder' ),
			'new_item'           => __( 'New Field Group', 'meta-box-builder' ),
			'edit_item'          => __( 'Edit Field Group', 'meta-box-builder' ),
			'view_item'          => __( 'View Field Group', 'meta-box-builder' ),
			'all_items'          => __( 'Custom Fields', 'meta-box-builder' ),
			'search_items'       => __( 'Search', 'meta-box-builder' ),
			'parent_item_colon'  => __( 'Parent Field Groups:', 'meta-box-builder' ),
			'not_found'          => __( 'No field groups found.', 'meta-box-builder' ),
			'not_found_in_trash' => __( 'No field groups found in Trash.', 'meta-box-builder' ),
		];

		$args = [
			'labels'          => $labels,
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'meta-box',
			'rewrite'         => false,
			'capability_type' => 'post',
			'supports'        => [ 'title' ],

			'map_meta_cap'    => true,
			'capabilities'    => [
				// Meta capabilities.
				'edit_post'              => 'edit_meta_box',
				'read_post'              => 'read_meta_box',
				'delete_post'            => 'delete_meta_box',

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

		register_post_type( 'meta-box', $args );
	}

	public function updated_messages( $messages ) {
		$post = get_post();

		$messages['meta-box'] = [
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Field group updated.', 'meta-box-builder' ),
			2 => __( 'Custom field updated.', 'meta-box-builder' ),
			3 => __( 'Custom field deleted.', 'meta-box-builder' ),
			4 => __( 'Field group updated.', 'meta-box-builder' ),
			// translators: %s - date and time of the revision
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'Field group restored to revision from %s', 'meta-box-builder' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			6 => __( 'Field group published.', 'meta-box-builder' ),
			7 => __( 'Field group saved.', 'meta-box-builder' ),
			8 => __( 'Field group submitted.', 'meta-box-builder' ),
			// translators: %s - post scheduled time.
			9  => sprintf( __( 'Field group scheduled for: <strong>%s</strong>.', 'meta-box-builder' ), date_i18n( __( 'M j, Y @ G:i', 'meta-box-builder' ), strtotime( $post->post_date ) ) ),
			// translators: %s - post type singular label.
			10 => __( 'Field group draft updated.', 'meta-box-builder' ),
		];

		return $messages;
	}
}
