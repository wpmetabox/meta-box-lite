<?php
namespace MBB\Extensions\CustomModel;

use MBB\BaseEditPage;
use MBB\Assets;
use MetaBox\Support\Data;

class Edit extends BaseEditPage {
	public function __construct( string $post_type ) {
		parent::__construct( $post_type );

		add_action( 'add_meta_boxes', [ $this, 'remove_submitdiv_meta_box' ] );
	}

	public function remove_submitdiv_meta_box(): void {
		remove_meta_box( 'submitdiv', $this->post_type, 'side' );
	}

	public function enqueue(): void {
		wp_enqueue_style( 'wp-edit-post' );

		wp_enqueue_style( 'mbb-app', MBB_URL . 'assets/css/style.css', [ 'wp-components', 'code-editor' ], filemtime( MBB_DIR . 'assets/css/style.css' ) );

		wp_enqueue_style(
			'mb-custom-model-app',
			MBB_URL . 'src/Extensions/CustomModel/css/custom-model.css',
			[ 'mbb-app' ],
			filemtime( MBB_DIR . 'src/Extensions/CustomModel/css/custom-model.css' )
		);
		Assets::enqueue_font_awesome();

		wp_enqueue_code_editor( [ 'type' => 'application/x-httpd-php' ] );

		$asset = require __DIR__ . '/build/custom-model.asset.php';

		$asset['dependencies'] = array_merge( $asset['dependencies'], [ 'jquery', 'clipboard', 'code-editor' ] );
		wp_enqueue_script(
			'mb-custom-model-app',
			MBB_URL . 'src/Extensions/CustomModel/build/custom-model.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		global $wpdb;

		$data = [
			'settings'       => get_post_meta( get_the_ID(), 'settings', true ) ?: [],
			'icons'          => Data::get_dashicons(),
			'action'         => get_current_screen()->action,
			'url'            => admin_url( 'edit.php?post_type=' . get_current_screen()->id ),
			'menu_positions' => $this->get_menu_positions(),
			'menu_parents'   => $this->get_menu_parents(),
			'capabilities'   => $this->get_capabilities(),
			'tablePrefix'    => $wpdb->prefix,
			'texts'          => [
				'saving' => __( 'Saving...', 'meta-box-builder' ),
			],
		];

		wp_localize_script( 'mb-custom-model-app', 'MbbApp', $data );
	}

	private function get_capabilities(): array {
		$caps  = [];
		$roles = wp_roles();
		foreach ( $roles->roles as $role ) {
			$caps = array_merge( $caps, array_keys( $role['capabilities'] ) );
		}

		$caps = array_unique( $caps );
		sort( $caps );

		return $caps;
	}
}
