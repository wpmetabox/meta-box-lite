<?php
namespace MBB\Integrations\WPML;

use WP_Post;

class SettingsPage {
	private $keys = [];

	public function __construct() {
		$this->keys = [
			'menu_title'    => __( 'Title', 'meta-box-builder' ),
			'submit_button' => __( 'Submit button', 'meta-box-builder' ),
			'message'       => __( 'Custom message', 'meta-box-builder' ),
		];

		add_action( 'save_post_mb-settings-page', [ $this, 'register_package' ], 20, 2 );
		add_filter( 'mbb_settings_page', [ $this, 'use_translations' ], 10, 2 );
		add_action( 'deleted_post_mb-settings-page', [ $this, 'delete_package' ], 10, 2 );
	}

	public function register_package( int $post_id, WP_Post $post ): void {
		$settings_page = get_post_meta( $post_id, 'settings_page', true );
		if ( empty( $settings_page ) || ! is_array( $settings_page ) ) {
			return;
		}

		$package = $this->get_package( $post );

		do_action( 'wpml_start_string_package_registration', $package );

		$this->register_strings( $settings_page, $package );

		do_action( 'wpml_delete_unused_package_strings', $package );
	}

	private function register_strings( array $settings_page, array $package ): void {
		foreach ( $this->keys as $key => $label ) {
			do_action(
				'wpml_register_string',
				$settings_page[ $key ] ?? '',
				$key,
				$package,
				$label,
				'LINE'
			);
		}
	}

	public function use_translations( array $settings_page, WP_Post $post ): array {
		$package = $this->get_package( $post );

		foreach ( $this->keys as $key => $label ) {
			if ( ! empty( $settings_page[ $key ] ) ) {
				$settings_page[ $key ] = apply_filters( 'wpml_translate_string', $settings_page[ $key ], $key, $package );
			}
		}

		return $settings_page;
	}

	private function get_package( WP_Post $post ): array {
		return [
			'kind'      => 'Meta Box: Settings Page',
			'name'      => urldecode( $post->post_name ),
			'title'     => $post->post_title,
			'edit_link' => get_edit_post_link( $post ),
			'view_link' => admin_url( "admin.php?page={$post->post_name}" ),
		];
	}

	public function delete_package( int $post_id, WP_Post $post ) {
		$package = $this->get_package( $post );
		do_action( 'wpml_delete_package', $package['name'], $package['kind'] );
	}
}
