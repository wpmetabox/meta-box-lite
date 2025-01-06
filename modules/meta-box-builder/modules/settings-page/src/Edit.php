<?php
namespace MBB\SettingsPage;

use MBB\BaseEditPage;
use MetaBox\Support\Data;

class Edit extends BaseEditPage {
	public function add_meta_boxes( $meta_boxes ) {
		$request  = rwmb_request();
		$settings = get_post_meta( $request->get( 'post' ), 'settings', true );

		$meta_boxes[] = [
			'title'      => $this->slug_meta_box_title,
			'post_types' => [ $this->post_type ],
			'context'    => 'side',
			'priority'   => 'low',
			'fields'     => [
				[
					'type' => 'text',
					'id'   => 'post_name',
					'name' => __( 'ID', 'meta-box-builder' ),
				],
				[
					'type' => 'custom_html',
					'std'  => '<a class="toggle_option_name" href="javascript:void(0)">' . __( 'Advanced', 'meta-box-builder' ) . '</a>',
				],
				[
					'type'    => 'text',
					'id'      => 'settings[option_name]',
					'name'    => __( 'Option name', 'meta-box-builder' ),
					'tooltip' => __( 'Option name where settings data is saved to. Takes settings page ID if missed. If you want to use theme mods, then set this to <code>theme_mods_$themeslug</code>.', 'meta-box-builder' ),
					'std'     => ! empty( $settings ) && isset( $settings['option_name'] ) ? $settings['option_name'] : '',
				],
			],
		];
		return $meta_boxes;
	}

	public function enqueue() {
		$url = MBB_URL . 'modules/settings-page/assets';

		wp_enqueue_style( 'mb-settings-page-ui', "$url/settings-page.css", [ 'wp-components' ], MBB_VER );
		wp_enqueue_style( 'font-awesome', MBB_URL . 'assets/fontawesome/css/all.min.css', [], '6.6.0' );

		wp_enqueue_code_editor( [ 'type' => 'application/x-httpd-php' ] );
		wp_enqueue_script( 'mb-settings-page-ui', "$url/settings-page.js", [ 'jquery', 'wp-element', 'wp-components', 'wp-i18n', 'clipboard' ], MBB_VER, true );

		$data = [
			'settings'       => get_post_meta( get_the_ID(), 'settings', true ),
			'icons'          => Data::get_dashicons(),

			'rest'           => untrailingslashit( rest_url() ),
			'nonce'          => wp_create_nonce( 'wp_rest' ),

			'menu_positions' => $this->get_menu_positions(),
			'menu_parents'   => $this->get_menu_parents(),
		];

		wp_localize_script( 'mb-settings-page-ui', 'MbbApp', $data );
	}

	public function save( $post_id, $post ) {
		// Set post title and slug in case they're auto-generated.
		$settings = array_merge( [
			'menu_title' => $post->post_title,
			'id'         => $post->post_name,
		], rwmb_request()->post( 'settings' ) );

		$parser = new Parser( $settings );
		$parser->parse_boolean_values()->parse_numeric_values();
		update_post_meta( $post_id, 'settings', $parser->get_settings() );

		$parser->parse();
		update_post_meta( $post_id, 'settings_page', $parser->get_settings() );
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
}
