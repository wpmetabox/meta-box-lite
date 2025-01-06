<?php
namespace MBB\SettingsPage;

use WP_REST_Server;
use WP_REST_Request;

class Generator {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route( 'mbb', 'settings-page-generate', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'generate' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function has_permission() {
		return current_user_can( 'manage_options' );
	}

	public function generate( WP_REST_Request $request ) {
		$settings = array_merge( [
			'menu_title' => $request->get_param( 'post_title' ),
			'id'         => $request->get_param( 'post_name' ) ?: sanitize_title( $request->get_param( 'post_title' ) ),
		], $request->get_param( 'settings' ) );

		$parser = new Parser( $settings );
		$parser->parse();

		$encoder = new Encoder( $parser->get_settings() );
		$encoder->encode();

		return $encoder->get_encoded_string();
	}
}
