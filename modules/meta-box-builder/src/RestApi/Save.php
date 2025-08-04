<?php
namespace MBB\RestApi;

use WP_REST_Server;
use WP_REST_Request;
use WP_Error;
use MBBParser\Parsers\Base as BaseParser;
use MBBParser\Parsers\MetaBox as MetaBoxParser;

class Save extends Base {
	public function register_routes(): void {
		register_rest_route( 'mbb', 'save', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'save' ],
			'permission_callback' => [ $this, 'has_permission' ],
			'args'                => [
				'post_id' => [
					'required' => true,
					'validate_callback' => function( $param ): bool {
						return is_numeric( $param );
					},
					'sanitize_callback' => 'absint',
				],
				'post_title' => [
					'validate_callback' => function( $param ) {
						if ( empty( $param ) ) {
							return new WP_Error( 'rest_invalid_param', __( 'Please enter the field group title', 'meta-box-builder' ), [ 'status' => 400 ] );
						}
						return true;
					},
					'sanitize_callback' => 'sanitize_text_field',
				],
				'post_name' => [
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );
	}

	public function save( WP_REST_Request $request ): array {
		$post_id     = $request->get_param( 'post_id' );
		$post_title  = $request->get_param( 'post_title' );
		$post_name   = $request->get_param( 'post_name' );
		$fields      = $request->get_param( 'fields' );
		$settings    = $request->get_param( 'settings' );

		if ( ! $post_name ) {
			$post_name = sanitize_title( $post_title );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [
				'success' => false,
				'message' => __( 'The field group might have been deleted. Please refresh the page and try again.', 'meta-box-builder' ),
			];
		}

		// Create (publish) the post if it's auto-draft.
		$post_status = $post->post_status;
		if ( ! in_array( $post_status, [ 'publish', 'draft' ], true ) ) {
			$post_status = 'publish';
		}

		$result = wp_update_post( [
			'ID'          => $post_id,
			'post_title'  => $post_title,
			'post_name'   => $post_name,
			'post_status' => $post_status,
		] );

		if ( is_wp_error( $result ) ) {
			return [
				'success' => false,
				'message' => $result->get_error_message(),
			];
		}

		// Save fields, settings and data
		$base_parser = new BaseParser();

		$settings = apply_filters( 'mbb_save_settings', $settings, $request );
		$base_parser->set_settings( $settings )->parse_boolean_values()->parse_numeric_values();
		update_post_meta( $post_id, 'settings', $base_parser->get_settings() );

		$fields = apply_filters( 'mbb_save_fields', $fields, $request );
		$base_parser->set_settings( $fields )->parse_boolean_values()->parse_numeric_values();
		update_post_meta( $post_id, 'fields', $base_parser->get_settings() );

		// Save parsed data for PHP (serialized array)
		$submitted_data = compact( 'fields', 'settings' );
		$submitted_data = apply_filters( 'mbb_save_submitted_data', $submitted_data, $request );

		// Set post title and slug in case they're auto-generated
		$submitted_data['post_title'] = $post_title;
		$submitted_data['post_name']  = $post_name;

		$parser = new MetaBoxParser( $submitted_data );
		$parser->parse();

		update_post_meta( $post_id, 'meta_box', $parser->get_settings() );

		// Allow developers to add actions after saving the meta box
		do_action( 'mbb_after_save', $parser, $post_id, $submitted_data );

		return [
			'success' => true,
			'message' => __( 'Data saved successfully', 'meta-box-builder' )
		];
	}
}