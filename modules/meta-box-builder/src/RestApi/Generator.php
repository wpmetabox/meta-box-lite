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

	public function register_routes(): void {
		register_rest_route( 'mbb', 'generate', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'generate' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Generate PHP code for the field group.
	 * The generated code is the same as post meta's 'meta_box' key.
	 * @see Save::save()
	 *
	 * @param WP_REST_Request $request
	 * @return array|array{message: string, success: bool|string}
	 */
	public function generate( WP_REST_Request $request ) {
		$post_title  = sanitize_text_field( $request->get_param( 'post_title' ) );
		$post_name   = sanitize_text_field( $request->get_param( 'post_name' ) );
		$fields      = $request->get_param( 'fields' );
		$settings    = $request->get_param( 'settings' );

		if ( ! $post_title ) {
			return [
				'success' => false,
				'message' => __( 'Invalid data', 'meta-box-builder' ),
			];
		}

		if ( ! $post_name ) {
			$post_name = sanitize_title( $post_title );
		}

		// Save fields, settings and data
		$settings = apply_filters( 'mbb_save_settings', $settings, $request );
		$fields   = apply_filters( 'mbb_save_fields', $fields, $request );

		// Save parsed data for PHP (serialized array)
		$submitted_data = compact( 'fields', 'settings' );
		$submitted_data = apply_filters( 'mbb_save_submitted_data', $submitted_data, $request );

		// Set post title and slug in case they're auto-generated
		$submitted_data['post_title'] = $post_title;
		$submitted_data['post_name']  = $post_name;

		$parser = new Parser( $submitted_data );
		$parser->parse();

		$settings = $parser->get_settings();
		// 'modified' is used only for location JSON, not in PHP code
		unset( $settings['modified'] );
		$encoder  = new Encoder( $settings );
		$encoder->encode();

		return $encoder->get_encoded_string();
	}
}
