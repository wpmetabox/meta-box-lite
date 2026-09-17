<?php
namespace MBB\Extensions\CustomModel;

use WP_REST_Request;
use WP_REST_Server;
use WP_Error;
use MBB\Helpers\Data;
use MBB\Helpers\Id;
use MBB\Helpers\TableSchema;
use MBB\LocalJson;
use MBB\RestApi\Save as SaveRestApi;

class Save {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes(): void {
		register_rest_route( 'mbb', 'custom-model/save', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'save' ],
			'permission_callback' => [ $this, 'has_permission' ],
			'show_in_index'       => false,
			'args'                => [
				'post_id'    => $this->get_post_id_arg(),
				'post_title' => [
					'validate_callback' => function ( $param ) {
						if ( empty( $param ) ) {
							return new WP_Error( 'rest_invalid_param', __( 'Please enter the custom model title', 'meta-box-builder' ), [ 'status' => 400 ] );
						}
						return true;
					},
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );

		register_rest_route( 'mbb', 'custom-model/columns', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'update_columns' ],
			'permission_callback' => [ $this, 'has_permission' ],
			'show_in_index'       => false,
			'args'                => [
				'post_id' => $this->get_post_id_arg(),
			],
		] );

		register_rest_route( 'mbb', 'custom-model/create-table', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'create_table' ],
			'permission_callback' => [ $this, 'has_permission' ],
			'show_in_index'       => false,
			'args'                => [
				'table' => [
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'model' => [
					'required'          => false,
					'sanitize_callback' => 'sanitize_key',
				],
			],
		] );

		register_rest_route( 'mbb', 'custom-model/table-columns', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_table_columns' ],
			'permission_callback' => [ $this, 'has_permission' ],
			'show_in_index'       => false,
			'args'                => [
				'model' => [
					'required'          => false,
					'sanitize_callback' => 'sanitize_key',
				],
				'table' => [
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );
	}

	public function has_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function save( WP_REST_Request $request ): array {
		$post_id    = (int) $request->get_param( 'post_id' );
		$post_title = (string) $request->get_param( 'post_title' );
		$settings   = $request->get_param( 'settings' );

		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		$post_name         = Id::sanitize( empty( $settings['slug'] ) ? $post_title : $settings['slug'], $post_title );
		$settings['table'] = TableSchema::sanitize_name( (string) ( $settings['table'] ?? '' ) );

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [
				'success' => false,
				'message' => __( 'The custom model might have been deleted. Please refresh the page and try again.', 'meta-box-builder' ),
			];
		}

		if ( empty( $settings['table'] ) ) {
			return [
				'success' => false,
				'message' => __( 'Please enter the custom table name.', 'meta-box-builder' ),
			];
		}

		$previous_id = $post->post_name;

		// Create (publish) the post if it's auto-draft.
		$post_status = $post->post_status;
		if ( ! in_array( $post_status, [ 'publish', 'draft' ], true ) ) {
			$post_status = 'publish';
		}

		if ( 'publish' === $post_status ) {
			$json_error = LocalJson::check_id( 'mb-model', $post_name, $previous_id );
			if ( '' !== $json_error ) {
				return [
					'success' => false,
					'message' => $json_error,
				];
			}
		}

		$update_args = [
			'ID'          => $post_id,
			'post_title'  => $post_title,
			'post_name'   => $post_name,
			'post_status' => $post_status,
			'post_date'   => $post->post_date,
		];
		$update_args = SaveRestApi::fix_post_date( $update_args );

		$needs_update = $post->post_title !== $post_title
			|| $post->post_name !== $post_name
			|| $post->post_status !== $post_status
			|| $update_args['post_date'] !== $post->post_date;

		if ( $needs_update ) {
			$result = wp_update_post( $update_args );

			if ( is_wp_error( $result ) ) {
				return [
					'success' => false,
					'message' => $result->get_error_message(),
				];
			}
		}

		$settings['slug'] = $post_name;
		if ( empty( $settings['labels']['name'] ) ) {
			$settings['labels']['name'] = $post_title;
		}
		if ( empty( $settings['labels']['singular_name'] ) ) {
			$settings['labels']['singular_name'] = $post_title;
		}
		if ( empty( $settings['labels']['menu_name'] ) ) {
			$settings['labels']['menu_name'] = $settings['labels']['name'];
		}

		return self::persist_model( $post_id, $post_name, $settings, $previous_id );
	}

	/**
	 * Update only the columns schema for an existing model (used by field group modal).
	 */
	public function update_columns( WP_REST_Request $request ): array {
		$post_id = (int) $request->get_param( 'post_id' );
		$columns = $request->get_param( 'columns' );
		$post    = get_post( $post_id );

		if ( ! $post || 'mb-model' !== $post->post_type ) {
			return [
				'success' => false,
				'message' => __( 'The custom model might have been deleted. Please refresh the page and try again.', 'meta-box-builder' ),
			];
		}

		$settings = get_post_meta( $post_id, 'settings', true );
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		$settings['columns'] = is_array( $columns ) ? $columns : [];
		$post_name           = Id::sanitize( $post->post_name ?: $post->post_title, $post->post_title );

		$result = self::persist_model( $post_id, $post_name, $settings );
		if ( ! $result['success'] ) {
			return $result;
		}

		$payload = Data::format_model( $post_name );
		if ( ! empty( $payload['table'] ) ) {
			$model                 = get_post_meta( $post_id, 'model', true );
			$supports              = isset( $model['supports'] ) && is_array( $model['supports'] ) ? $model['supports'] : [];
			$inspected             = TableColumns::inspect( $payload['table'], $supports );
			$payload['db_columns'] = $inspected['columns'];
			if ( empty( $payload['keys'] ) ) {
				$payload['keys'] = $inspected['keys'];
			}
		}

		return [
			'success' => true,
			'message' => __( 'Model table schema updated.', 'meta-box-builder' ),
			'model'   => $payload,
		];
	}

	public function create_table( WP_REST_Request $request ): array {
		$columns = $request->get_param( 'columns' );

		return TableColumns::create(
			(string) $request->get_param( 'table' ),
			is_array( $columns ) ? $columns : [],
			(string) $request->get_param( 'model' )
		);
	}

	public function get_table_columns( WP_REST_Request $request ): array {
		return TableColumns::list_for_model(
			(string) $request->get_param( 'model' ),
			(string) $request->get_param( 'table' )
		);
	}

	/**
	 * Persist model settings, register the model, and sync Local JSON.
	 *
	 * @param int    $post_id     Model post ID.
	 * @param string $post_name   Model slug.
	 * @param array  $settings    Raw settings from the editor.
	 * @param string $previous_id Slug before save; used to remove the old Local JSON file after rename.
	 */
	public static function persist_model( int $post_id, string $post_name, array $settings, string $previous_id = '' ): array {
		$settings['modified'] = time();

		$parser = new Parser( $settings );
		$parser->parse_boolean_values()->parse_numeric_values();

		// UI-only locks; derived again on editor load from slug/table vs labels.
		$settings_data = $parser->get_settings();
		unset( $settings_data['_slug_changed'], $settings_data['_table_changed'] );

		$parser->parse();
		$model         = $parser->get_settings();
		$model['name'] = $post_name;

		// Register before DDL so mbct_table_schema can add AUTO_INCREMENT and supports.
		$model['post_id'] = $post_id;
		Register::register( $post_name, $model );
		$table_result = Register::create_table( $model, true );
		if ( true !== $table_result ) {
			return [
				'success' => false,
				'message' => is_string( $table_result )
					? $table_result
					: __( 'Could not create or update the database table.', 'meta-box-builder' ),
			];
		}

		unset( $model['post_id'] );
		update_post_meta( $post_id, 'settings', $settings_data );
		update_post_meta( $post_id, 'model', $model );

		Register::rebuild_cache();

		$args = [
			'post_id'   => $post_id,
			'post_type' => 'mb-model',
		];
		if ( $previous_id !== '' && $previous_id !== $post_name ) {
			$args['previous_id'] = $previous_id;
		}

		LocalJson::use_database( $args );

		$json_error = LocalJson::get_last_error();
		if ( '' !== $json_error ) {
			return [
				'success' => false,
				'message' => $json_error,
			];
		}

		return [
			'success' => true,
			'message' => __( 'Custom model is updated.', 'meta-box-builder' ),
		];
	}

	/**
	 * REST route argument schema for a model post ID.
	 *
	 * @return array<string, mixed>
	 */
	private function get_post_id_arg(): array {
		return [
			'required'          => true,
			'validate_callback' => function ( $param ): bool {
				return is_numeric( $param );
			},
			'sanitize_callback' => 'absint',
		];
	}
}
