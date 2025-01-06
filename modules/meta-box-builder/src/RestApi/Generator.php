<?php
namespace MBB\RestApi;

use MBBParser\Parsers\MetaBox as Parser;
use MBBParser\Encoders\MetaBox as Encoder;
use WP_REST_Server;
use WP_REST_Request;

class Generator {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route( 'mbb', 'generate', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'generate' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function has_permission() {
		return current_user_can( 'manage_options' );
	}

	public function generate( WP_REST_Request $request ) {
		$parser = new Parser( $request->get_params() );
		$parser->parse();

		$settings = $parser->get_settings();
		$encoder  = new Encoder( $settings );
		$encoder->encode();

		return $encoder->get_encoded_string();
	}
}
