<?php
namespace MBBParser;

use WP_REST_Server;
use WP_REST_Request;

class RestApi {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route( 'mbb-parser', 'meta-box', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'generate_code' ],
			'permission_callback' => '__return_true',
		] );
	}

	public function generate_code( WP_REST_Request $request ) {
		$parser = new Parsers\MetaBox( $request->get_params() );
		$parser->parse();

		$settings = $parser->get_settings();
		$encoder  = new Encoders\MetaBox( $settings );
		$encoder->encode();

		return $encoder->get_encoded_string();
	}
}
