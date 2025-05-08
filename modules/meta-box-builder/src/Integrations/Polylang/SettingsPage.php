<?php
namespace MBB\Integrations\Polylang;

use WP_Post;

class SettingsPage {
	private $keys = [];

	public function __construct() {
		$this->keys = [
			'menu_title'    => __( 'Title', 'meta-box-builder' ),
			'submit_button' => __( 'Submit button', 'meta-box-builder' ),
			'message'       => __( 'Custom message', 'meta-box-builder' ),
		];

		add_filter( 'mbb_settings_page', [ $this, 'register_strings' ], 10 );
		add_filter( 'mbb_settings_page', [ $this, 'use_translations' ], 20 );
	}

	public function register_strings( array $settings_page ): array {
		if ( empty( $settings_page ) || ! is_array( $settings_page ) ) {
			return $settings_page;
		}

		$context = $this->get_context( $settings_page );

		foreach ( $this->keys as $key => $label ) {
			pll_register_string( $key, $settings_page[ $key ] ?? '', $context );
		}

		return $settings_page;
	}

	public function use_translations( array $settings_page ): array {
		foreach ( $this->keys as $key => $label ) {
			if ( ! empty( $settings_page[ $key ] ) ) {
				$settings_page[ $key ] = pll__( $settings_page[ $key ] );
			}
		}

		return $settings_page;
	}

	private function get_context( array $settings_page ): string {
		// translators: %s is the title of the settings page.
		return sprintf( __( 'Meta Box Settings Page: %s', 'meta-box-builder' ), $settings_page['menu_title'] ?? '' );
	}
}