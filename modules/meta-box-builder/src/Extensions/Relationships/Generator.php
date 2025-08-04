<?php
namespace MBB\Extensions\Relationships;

use WP_REST_Server;
use WP_REST_Request;

class Generator {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route( 'mbb', 'relationships/generate', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'generate' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function has_permission() {
		return current_user_can( 'manage_options' );
	}

	public function generate( WP_REST_Request $request ) {
		$post_title = sanitize_text_field( $request->get_param( 'post_title' ) );
		if ( ! $post_title ) {
			return __( 'Please enter a title for the relationship.', 'meta-box-builder' );
		}

		$settings   = $request->get_param( 'settings' );

		$settings['id'] = sanitize_title( empty( $settings['id'] ) ? $post_title : $settings['id'] );

		$parser = new Parsers\Relationship( $settings );
		$parser->parse();

		$settings = $parser->get_settings();
		$encoder  = new Encoders\Relationship( $settings );
		$encoder->encode();

		return $encoder->get_encoded_string();
	}
}
