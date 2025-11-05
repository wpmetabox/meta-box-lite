<?php
namespace MBB;

class LocalJson {
	public function __construct() {
		add_action( 'mbb_after_save', [ $this, 'generate_local_json' ], 10, 3 );
	}

	public function generate_local_json( $parser, $post_id, $raw_data ): bool {
		return self::use_database( compact( 'post_id' ) );
	}

	/**
	 * Check if the local JSON feature is enabled
	 *
	 * @return bool
	 */
	public static function is_enabled(): bool {
		return ! empty( JsonService::get_paths() );
	}

	/**
	 * Get decoded JSON as an associative array from a .json file
	 */
	public static function read_file( string $file_path ): array {
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return [];
		}

		$content = file_get_contents( $file_path );
		$json    = json_decode( $content, true );

		return is_array( $json ) ? $json : [];
	}

	public static function write_file( string $file_path, array $data ) {
		if ( ! is_writable( dirname( $file_path ) ) ) {
			return false;
		}

		if ( ! is_dir( dirname( $file_path ) ) ) {
			wp_mkdir_p( dirname( $file_path ) );
		}

		$output = wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

		return @file_put_contents( $file_path, $output );
	}

	/**
	 * Import from .json file
	 *
	 * @return \WP_Error|boolean
	 */
	public static function import( array $data ): bool {
		return self::sync_json( $data );
	}

	public static function import_many( array $json ): void {
		foreach ( $json as $data ) {
			self::import( $data );
		}
	}

	/**
	 * Use local json file and override database. Currently, its using by REST API
	 *
	 * @param array $args
	 * @return bool Success or not
	 */
	public static function use_json( array $args ): bool {
		$json = JsonService::get_json( [
			'id'        => $args['post_name'],
			'post_type' => $args['post_type'],
		] );

		if ( ! $json || ! is_array( $json ) ) {
			return false;
		}

		$json = reset( $json );

		return self::sync_json( $json );
	}

	/**
	 * Sync from JSON file to database.
	 *
	 * Exprected format: [ 'post_id' => 123, 'local' => [local JSON array] ]. See JsonService::get_json() for the full format.
	 * After unparsing: 'local' becomes:
	 * [
	 *      // Post fields:
	 *      'post_type',
	 *      'post_name',
	 *      'post_title',
	 *      'post_date',
	 *      'post_status',
	 *      'post_content',
	 *
	 *      // Meta keys (same as exported)
	 *      'settings',
	 *      'meta_box',
	 *      'fields',
	 *      'data',
	 * ]
	 *
	 * @param array $data
	 * @return bool Success or not
	 */
	public static function sync_json( array $data ): bool {
		$required_keys = [ 'post_id', 'local' ];

		foreach ( $required_keys as $key ) {
			if ( ! array_key_exists( $key, $data ) ) {
				return false;
			}
		}

		$post_array = [ 'ID' => $data['post_id'] ];
		$data       = $data['local'];
		$unparser   = new \MBBParser\Unparsers\MetaBox( $data );
		$unparser->unparse();
		$data        = $unparser->get_settings();
		$meta_fields = Export::get_meta_keys( $data['post_type'] );
		$post_array  = array_merge( $post_array, [
			'post_type'    => $data['post_type'],
			'post_name'    => $data['post_name'],
			'post_title'   => $data['post_title'],
			'post_date'    => $data['post_date'],
			'post_status'  => $data['post_status'],
			'post_content' => $data['post_content'],
		] );

		$post_id = wp_insert_post( $post_array );

		foreach ( $meta_fields as $meta_key ) {
			if ( ! isset( $data[ $meta_key ] ) ) {
				continue;
			}

			update_post_meta( $post_id, $meta_key, $data[ $meta_key ] );
		}

		// Now we need to save the modified data back to the JSON file
		self::use_database( [ 'post_id' => $post_id ] );

		return true;
	}

	/**
	 * Sync data from database to JSON file, overwriting existing content.
	 *
	 * @param array $args Contains either `post_id` or `post_name`.
	 * @return bool Success or not
	 */
	public static function use_database( array $args = [] ): bool {
		if ( ! self::is_enabled() ) {
			return false;
		}

		$post = null;
		if ( isset( $args['post_id'] ) ) {
			$post = get_post( $args['post_id'] );
		} elseif ( isset( $args['post_name'] ) ) {
			$post = get_page_by_path( $args['post_name'], OBJECT, 'meta-box' );
		}

		if ( empty( $post ) || $post->post_type !== 'meta-box' || $post->post_status !== 'publish' ) {
			return false;
		}

		$post_data             = (array) $post;
		$meta_box              = get_post_meta( $post->ID, 'meta_box', true ) ?: [];
		$post_data['meta_box'] = $meta_box;
		$settings              = get_post_meta( $post->ID, 'settings', true );
		$post_data['settings'] = (array) $settings;

		$unparser = new \MBBParser\Unparsers\MetaBox( $post_data );
		$unparser->unparse();
		$post_data = $unparser->to_minimal_format();

		// By default, we will save the file in the first path with {$post->post_name}.json
		// however, some users might store the file name different with the meta box ID
		// so we need to make an additional check if the file exists and write to that file instead
		// of writing to the new file.
		$files     = JsonService::get_files();
		$file_path = JsonService::get_paths()[0] . '/' . $post->post_name . '.json';
		foreach ( $files as $file ) {
			$raw_json = self::read_file( $file );
			if ( empty( $raw_json ) ) {
				continue;
			}

			$unparser = new \MBBParser\Unparsers\MetaBox( $raw_json );
			$unparser->unparse();
			$json = $unparser->get_settings();

			if ( $json['meta_box']['id'] !== $post->post_name ) {
				continue;
			}

			$file_path = $file;
			break;
		}

		return (bool) self::write_file( $file_path, $post_data );
	}
}
