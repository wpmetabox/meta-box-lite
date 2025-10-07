<?php
namespace MBB\Extensions\SettingsPage;

use MBB\BaseEditPage;
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
		wp_enqueue_style( 'font-awesome', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css', [], ' 6.7.2' );

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

	private function get_menu_positions() {
		global $menu;
		$positions = [];
		foreach ( $menu as $position => $params ) {
			if ( ! empty( $params[0] ) ) {
				$positions[ $position ] = $this->strip_span( $params[0] );
			}
		}
		return $positions;
	}

	private function get_menu_parents() {
		global $menu;
		$options = [];
		foreach ( $menu as $params ) {
			if ( ! empty( $params[0] ) && ! empty( $params[2] ) ) {
				$options[ $params[2] ] = $this->strip_span( $params[0] );
			}
		}
		return $options;
	}

	private function strip_span( $html ) {
		return preg_replace( '@<span .*>.*</span>@si', '', $html );
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
