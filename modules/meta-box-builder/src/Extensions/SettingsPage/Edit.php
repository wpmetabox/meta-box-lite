<?php
namespace MBB\Extensions\SettingsPage;

use MBB\BaseEditPage;
use MBB\Assets;
use MetaBox\Support\Data;

class Edit extends BaseEditPage {
	public function enqueue() {
		wp_enqueue_style( 'wp-edit-post' );

		wp_enqueue_style( 'mbb-app', MBB_URL . 'assets/css/style.css', [ 'wp-components', 'code-editor' ], filemtime( MBB_DIR . 'assets/css/style.css' ) );

		wp_enqueue_style(
			'mb-settings-page-app',
			MBB_URL . 'src/Extensions/SettingsPage/css/settings-page.css',
			[ 'wp-components', 'code-editor' ],
			filemtime( MBB_DIR . 'src/Extensions/SettingsPage/css/settings-page.css' )
		);
		Assets::enqueue_font_awesome();

		wp_enqueue_code_editor( [ 'type' => 'application/x-httpd-php' ] );

		$asset = require __DIR__ . '/build/settings-page.asset.php';

		// Add extra JS libs for copy code to clipboard & block color picker.
		$asset['dependencies'] = array_merge( $asset['dependencies'], [ 'jquery', 'clipboard', 'code-editor' ] );
		wp_enqueue_script(
			'mb-settings-page-app',
			MBB_URL . 'src/Extensions/SettingsPage/build/settings-page.js',
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
			'adminUrl'       => admin_url(),
			'url'            => admin_url( 'edit.php?post_type=' . get_current_screen()->id ),
			'title'          => $post->post_title,

			'settings'       => get_post_meta( get_the_ID(), 'settings', true ),
			'icons'          => Data::get_dashicons(),

			'menu_positions' => $this->get_menu_positions(),
			'menu_parents'   => $this->get_menu_parents(),
			'capabilities'   => $this->get_capabilities(),

			'texts'          => [
				'saving' => __( 'Saving...', 'meta-box-builder' ),
			],
		];

		wp_localize_script( 'mb-settings-page-app', 'MbbApp', $data );
	}

	private function get_capabilities() {
		$caps  = [];
		$roles = wp_roles();
		foreach ( $roles->roles as $role ) {
			$caps = array_merge( $caps, array_keys( $role['capabilities'] ) );
		}

		$caps = array_unique( $caps );
		sort( $caps );

		return array_combine( $caps, $caps );
	}
}
