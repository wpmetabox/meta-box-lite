<?php
namespace MBB\Extensions\Relationships;

use MBB\BaseEditPage;
use MBB\Helpers\Data;

class Edit extends BaseEditPage {
	public function enqueue() {
		wp_enqueue_style( 'wp-edit-post' );

		wp_enqueue_style( 'mbb-app', MBB_URL . 'assets/css/style.css', [ 'wp-components', 'code-editor' ], filemtime( MBB_DIR . 'assets/css/style.css' ) );

		wp_enqueue_style(
			'mb-relationships-app',
			MBB_URL . 'src/Extensions/Relationships/css/relationships.css',
			[ 'wp-components', 'code-editor' ],
			filemtime( MBB_DIR . 'src/Extensions/Relationships/css/relationships.css' )
		);

		wp_enqueue_code_editor( [ 'type' => 'application/x-httpd-php' ] );

		$asset = require __DIR__ . '/build/relationships.asset.php';

		// Add extra JS libs for copy code to clipboard & block color picker.
		$asset['dependencies'] = array_merge( $asset['dependencies'], [ 'jquery', 'clipboard', 'code-editor' ] );
		wp_enqueue_script(
			'mb-relationships-app',
			MBB_URL . 'src/Extensions/Relationships/build/relationships.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		// Script to toggle the admin menu.
		wp_enqueue_script(
			'mbb-admin-menu',
			MBB_URL . 'assets/js/admin-menu.js',
			[],
			filemtime( MBB_DIR . 'assets/js/admin-menu.js' ),
			true
		);

		$post = get_post();

		$data = [
			'adminUrl'     => admin_url(),
			'url'          => admin_url( 'edit.php?post_type=' . get_current_screen()->id ),
			'title'        => $post->post_title,

			'post_types'   => $this->get_post_types(),
			'taxonomies'   => $this->get_taxonomies(),
			'object_types' => $this->get_object_type_options(),

			'settings'     => get_post_meta( get_the_ID(), 'settings', true ),

			'texts'        => [
				'saving' => __( 'Saving...', 'meta-box-builder' ),
			],
		];

		wp_localize_script( 'mb-relationships-app', 'MbbApp', $data );
	}

	private function get_post_types(): array {
		$post_types = Data::get_post_types();
		$options    = [];
		foreach ( $post_types as $post_type ) {
			$options[ $post_type['slug'] ] = sprintf( '%s (%s)', $post_type['name'], $post_type['slug'] );
		}
		return $options;
	}

	private function get_taxonomies(): array {
		$taxonomies = Data::get_taxonomies();
		$options    = [];
		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy['slug'] ] = sprintf( '%s (%s)', $taxonomy['name'], $taxonomy['slug'] );
		}
		return $options;
	}

	private function get_object_type_options(): array {
		$options         = [];
		$options['post'] = __( 'Post', 'meta-box-builder' );
		if ( Data::is_extension_active( 'mb-term-meta' ) ) {
			$options['term'] = __( 'Term', 'meta-box-builder' );
		}
		if ( Data::is_extension_active( 'mb-user-meta' ) ) {
			$options['user'] = __( 'User', 'meta-box-builder' );
		}
		return $options;
	}
}
