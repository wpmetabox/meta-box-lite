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
				'post_id'    => [
					'required'          => true,
					'validate_callback' => function ( $param ): bool {
						return is_numeric( $param );
					},
					'sanitize_callback' => 'absint',
				],
				'post_title' => [
					'validate_callback' => function ( $param ) {
						if ( empty( $param ) ) {
							return new WP_Error( 'rest_invalid_param', __( 'Please enter the field group title', 'meta-box-builder' ), [ 'status' => 400 ] );
						}
						return true;
					},
					'sanitize_callback' => 'sanitize_text_field',
				],
				'post_name'  => [
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );
	}

	public function save( WP_REST_Request $request ): array {
		$post_id    = $request->get_param( 'post_id' );
		$post_title = $request->get_param( 'post_title' );
		$post_name  = $request->get_param( 'post_name' );
		$fields     = $request->get_param( 'fields' );
		$settings   = $request->get_param( 'settings' );

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

		$update_args = [
			'ID'          => $post_id,
			'post_title'  => $post_title,
			'post_name'   => $post_name,
			'post_status' => $post_status,
			'post_date'   => $post->post_date,
		];
		$update_args = self::fix_post_date( $update_args );

		$result = wp_update_post( $update_args );

		if ( is_wp_error( $result ) ) {
			return [
				'success' => false,
				'message' => $result->get_error_message(),
			];
		}

		$fields   = apply_filters( 'mbb_save_fields', $fields, $request );
		$settings = apply_filters( 'mbb_save_settings', $settings, $request );

		$parser = self::parse( $post, $fields, $settings, $post_title, $post_name );

		do_action( 'mbb_after_save', $parser, $post_id, compact( 'fields', 'settings', 'post_title', 'post_name' ) );

		return [
			'success' => true,
			'message' => __( 'Data saved successfully', 'meta-box-builder' ),
		];
	}

	public static function parse( \WP_Post $post, array $fields, array $settings, ?string $post_title = null, ?string $post_name = null ): MetaBoxParser {
		$base_parser = new BaseParser();

		$base_parser->set_settings( $settings )->parse_boolean_values()->parse_numeric_values();
		update_post_meta( $post->ID, 'settings', $base_parser->get_settings() );

		$base_parser->set_settings( $fields )->parse_boolean_values()->parse_numeric_values();
		update_post_meta( $post->ID, 'fields', $base_parser->get_settings() );

		$submitted_data = [
			'fields'     => $fields,
			'settings'   => $settings,
			'post_title' => $post_title ?? $post->post_title,
			'post_name'  => $post_name ?? $post->post_name,
		];

		$parser = new MetaBoxParser( $submitted_data );
		$parser->parse();

		update_post_meta( $post->ID, 'meta_box', $parser->get_settings() );

		return $parser;
	}

	public static function fix_post_date( array $args ): array {
		if ( empty( $args['post_date'] ) ) {
			return $args;
		}

		$now = current_time( 'mysql' );
		if ( $args['post_date'] <= $now ) {
			return $args;
		}

		// Fix post date if it's in the future.
		$args['post_date']     = $now;
		$args['post_date_gmt'] = current_time( 'mysql', true );

		return $args;
	}
}
