<?php
namespace MBB\Extensions\SettingsPage;

use WP_REST_Server;
use WP_REST_Request;

class Generator {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes(): void {
		register_rest_route( 'mbb', 'settings-page/generate', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'generate' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function generate( WP_REST_Request $request ) {
		$post_title = sanitize_text_field( $request->get_param( 'post_title' ) );
		if ( ! $post_title ) {
			return __( 'Please enter a title for the settings page.', 'meta-box-builder' );
		}

		$settings  = $request->get_param( 'settings' );
		$post_name = sanitize_title( empty( $settings['id'] ) ? $post_title : $settings['id'] );

		$settings['menu_title'] = $post_title;
		$settings['id']         = $post_name;
		if ( empty( $settings['option_name'] ) ) {
			$settings['option_name'] = $post_name;
		}

		$parser = new Parser( $settings );
		$parser->parse();

		$encoder = new Encoder( $parser->get_settings() );
		$encoder->encode();

		return $encoder->get_encoded_string();
	}
}
