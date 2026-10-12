<?php
namespace MBB;

use MBB\RestApi\Save;
use MBB\Helpers\Template;
use MBBParser\Unparsers\MetaBox;
use MetaBox\Support\Arr;

class LocalJson {
	public const SUPPORTED_POST_TYPES = [
		'meta-box',
		'mb-model',
		'mb-settings-page',
		'mb-relationship',
	];

	/**
	 * Why the latest sync did not write a file, empty when it wrote one or had nothing to do.
	 *
	 * @var string
	 */
	private static string $last_error = '';

	public function __construct() {
		add_action( 'mbb_after_save', [ $this, 'generate_local_json' ], 10, 3 );
		if ( is_admin() ) {
			new Template();
		}
	}

	public static function is_supported( string $post_type ): bool {
		return in_array( $post_type, self::SUPPORTED_POST_TYPES, true );
	}

	/**
	 * Sync error from the latest use_database() call, if any.
	 *
	 * Not every false return is an error: drafts and other post types simply have
	 * nothing to sync.
	 */
	public static function get_last_error(): string {
		return self::$last_error;
	}

	public function generate_local_json( $parser, $post_id, $raw_data ): bool {
		$args = compact( 'post_id' );
		if ( ! empty( $raw_data['previous_id'] ) ) {
			$args['previous_id'] = (string) $raw_data['previous_id'];
		}

		return self::use_database( $args );
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

		$json = wp_json_file_decode( $file_path, [ 'associative' => true ] );

		return is_array( $json ) ? $json : [];
	}

	/**
	 * Write encoded JSON to disk.
	 *
	 * @return int|false Bytes written, or false on failure.
	 */
	private static function write_file( string $file_path, array $data ) {
		// Create the directory first: a missing one is never writable.
		$dir = dirname( $file_path );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		if ( ! is_writable( $dir ) ) {
			return false;
		}

		$output = wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

		return @file_put_contents( $file_path, $output );
	}

	public static function import_many( array $json ): void {
		foreach ( $json as $data ) {
			self::sync_json( $data, false );
		}
		JsonService::clear_cache();
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
			'post_type' => $args['post_type'] ?? 'meta-box',
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
	 * @param array $data        Sync payload with post_id and local JSON.
	 * @param bool  $clear_cache Whether to clear the JsonService cache after writing.
	 * @return bool Success or not
	 */
	private static function sync_json( array $data, bool $clear_cache = true ): bool {
		$required_keys = [ 'post_id', 'local' ];

		foreach ( $required_keys as $key ) {
			if ( ! array_key_exists( $key, $data ) ) {
				return false;
			}
		}

		$post_array = [ 'ID' => $data['post_id'] ];
		$data       = $data['local'];
		$unparser   = new MetaBox( $data );
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
		$post_array  = Save::fix_post_date( $post_array );

		$post_id = wp_insert_post( $post_array );

		foreach ( $meta_fields as $meta_key ) {
			if ( ! isset( $data[ $meta_key ] ) ) {
				continue;
			}

			update_post_meta( $post_id, $meta_key, $data[ $meta_key ] );
		}

		do_action( 'mbb_sync_json', $data, $post_id );

		// Now we need to save the modified data back to the JSON file
		self::use_database( [
			'post_id'   => $post_id,
			'post_type' => $data['post_type'] ?? 'meta-box',
		], $clear_cache );

		return true;
	}

	/**
	 * Sync data from database to JSON file, overwriting existing content.
	 *
	 * @param array $args        Contains post_id or post_name, optional post_type and previous_id (old slug after rename).
	 * @param bool  $clear_cache Whether to clear the JsonService cache after writing.
	 * @return bool Success or not.
	 */
	public static function use_database( array $args = [], bool $clear_cache = true ): bool {
		self::$last_error = '';

		if ( ! self::is_enabled() ) {
			return false;
		}

		$post_type = $args['post_type'] ?? 'meta-box';
		$post      = null;
		if ( isset( $args['post_id'] ) ) {
			$post = get_post( $args['post_id'] );
		} elseif ( isset( $args['post_name'] ) ) {
			$post = get_page_by_path( $args['post_name'], OBJECT, $post_type );
		}

		if ( empty( $post ) || $post->post_status !== 'publish' ) {
			return false;
		}

		if ( ! self::is_supported( $post->post_type ) ) {
			return false;
		}

		$post_data = (array) $post;
		foreach ( Export::get_meta_keys( $post->post_type ) as $meta_key ) {
			$value                  = get_post_meta( $post->ID, $meta_key, true );
			$post_data[ $meta_key ] = is_array( $value ) ? $value : [];
		}

		$unparser = new MetaBox( $post_data );
		$unparser->unparse();
		$post_data = $unparser->to_minimal_format();

		$own_file  = self::find_own_file( $post->post_type, $post->post_name, (string) ( $args['previous_id'] ?? '' ) );
		$file_path = self::target_file( $post->post_type, $post->post_name, $own_file );
		if ( ! $file_path ) {
			return self::fail( self::id_taken_message() );
		}

		if ( ! self::write_file( $file_path, $post_data ) ) {
			return self::fail( __( 'Could not write the Local JSON file.', 'meta-box-builder' ) );
		}

		// A rename writes a new file, so the old one is left behind.
		if ( $own_file && $own_file !== $file_path ) {
			wp_delete_file( $own_file );
		}

		if ( $clear_cache ) {
			JsonService::clear_cache();
		}

		return true;
	}

	private static function fail( string $message ): bool {
		self::$last_error = $message;

		return false;
	}

	/**
	 * File currently storing this object, empty string when it has none yet.
	 *
	 * @param string $post_type   Builder post type.
	 * @param string $id          ID being saved.
	 * @param string $previous_id ID before this save.
	 */
	private static function find_own_file( string $post_type, string $id, string $previous_id ): string {
		return self::find_file_by_id( $post_type, $previous_id ?: $id );
	}

	/**
	 * File to write, empty string when another object owns the target ID or file name.
	 *
	 * @param string $post_type Builder post type.
	 * @param string $id        ID to store.
	 * @param string $own_file  File currently storing this object, if any.
	 */
	private static function target_file( string $post_type, string $id, string $own_file ): string {
		// Files are matched by the ID inside them, since users may rename files freely.
		$holder = self::find_file_by_id( $post_type, $id );
		if ( $holder ) {
			return $holder === $own_file ? $holder : '';
		}

		// Nothing stores the ID yet, so use {$id}.json unless a file already sits there.
		$path = JsonService::get_paths()[0] . '/' . $id . '.json';

		return $path === $own_file || ! self::read_file( $path ) ? $path : '';
	}

	/**
	 * Error when another object owns this ID, empty string when the ID is free.
	 *
	 * Saving checks this before writing the post, because the sync runs afterwards and
	 * would leave the new ID on the post while the file kept the old one.
	 *
	 * @param string $post_type   Builder post type.
	 * @param string $id          ID about to be saved.
	 * @param string $previous_id ID before this save.
	 */
	public static function check_id( string $post_type, string $id, string $previous_id = '' ): string {
		if ( ! self::is_enabled() ) {
			return '';
		}

		$own_file = self::find_own_file( $post_type, $id, $previous_id );

		return self::target_file( $post_type, $id, $own_file ) ? '' : self::id_taken_message();
	}

	private static function id_taken_message(): string {
		return __( 'Another JSON file already uses this ID. Please choose a different one.', 'meta-box-builder' );
	}

	/**
	 * Path of the JSON file storing an ID, empty string when no file matches.
	 *
	 * @param string $post_type Builder post type.
	 * @param string $id        ID stored in the JSON file.
	 */
	private static function find_file_by_id( string $post_type, string $id ): string {
		if ( ! $id ) {
			return '';
		}

		foreach ( JsonService::get_unparsed( $post_type ) as $item ) {
			if ( self::get_json_id( $item['data'] ) === $id ) {
				return $item['file'];
			}
		}

		return '';
	}

	/**
	 * ID used to match a JSON file to a Builder post.
	 */
	private static function get_json_id( array $data ): string {
		$keys = [
			'meta-box'         => [ 'meta_box.id', 'post_name' ],
			'mb-model'         => [ 'model.id', 'model.name', 'post_name' ],
			'mb-settings-page' => [ 'settings_page.id', 'post_name' ],
			'mb-relationship'  => [ 'relationship.id', 'post_name' ],
		];

		foreach ( $keys[ $data['post_type'] ?? 'meta-box' ] ?? [] as $key ) {
			$value = Arr::get( $data, $key );
			if ( $value ) {
				return (string) $value;
			}
		}

		return '';
	}
}
