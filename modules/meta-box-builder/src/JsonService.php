<?php
namespace MBB;

use MBB\Helpers\Path;
use MBBParser\Unparsers\MetaBox;
use WP_Query;

class JsonService {
	/**
	 * Unparsed Local JSON items grouped by post type for this request.
	 *
	 * @var array<string, array<int, array{file: string, raw: array, data: array, minimal: array}>>|null
	 */
	private static $unparsed = null;

	/**
	 * Per-post-type cache for get_json().
	 *
	 * @var array<string, array>
	 */
	private static $json_items = [];

	private const DIFF_JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

	/**
	 * Compare/sync payload for Local JSON vs database (cached per post type).
	 *
	 * Each item: file, local, local_minimized, is_newer (-1|0|1), post_id, post_type, id, remote, diff, is_writable.
	 *
	 * @param array $params Filters: post_type, id, post_id, file, is_newer, ….
	 */
	public static function get_json( array $params = [] ): array {
		$post_type = $params['post_type'] ?? 'meta-box';
		if ( ! isset( self::$json_items[ $post_type ] ) ) {
			// Cache the full post-type set; filter_items() applies post_id and other filters.
			self::$json_items[ $post_type ] = self::query_json( $post_type );
		}

		return self::filter_items( self::$json_items[ $post_type ], $params );
	}

	/**
	 * Unparsed Local JSON items for a post type (parsed once per request).
	 *
	 * Includes files with `"private": true` so registers can load them.
	 * Sync/compare UI excludes those via get_json().
	 *
	 * @return array<int, array{file: string, raw: array, data: array, minimal: array}>
	 */
	public static function get_unparsed( string $post_type ): array {
		self::ensure_unparsed_cache();

		return self::$unparsed[ $post_type ] ?? [];
	}

	/**
	 * Drop request caches after files change so later reads see fresh data.
	 */
	public static function clear_cache(): void {
		self::$unparsed   = null;
		self::$json_items = [];
	}

	private static function ensure_unparsed_cache(): void {
		if ( self::$unparsed !== null ) {
			return;
		}

		self::$unparsed = array_fill_keys( LocalJson::SUPPORTED_POST_TYPES, [] );

		foreach ( self::get_files() as $file ) {
			$item = self::parse_file( $file );
			if ( ! $item ) {
				continue;
			}

			self::$unparsed[ $item['data']['post_type'] ?? 'meta-box' ][] = $item;
		}
	}

	/**
	 * Parse one Local JSON file into raw, unparsed, and minimal forms.
	 *
	 * @return array{file: string, raw: array, data: array, minimal: array}|null
	 */
	private static function parse_file( string $file ): ?array {
		$raw = LocalJson::read_file( $file );
		if ( empty( $raw ) ) {
			return null;
		}

		$hint = self::detect_post_type_hint( $raw );
		if ( $hint !== '' && ! LocalJson::is_supported( $hint ) ) {
			return null;
		}

		$unparser = new MetaBox( $raw );
		$unparser->unparse();
		$data = $unparser->get_settings();
		if ( empty( $data ) ) {
			return null;
		}

		$type = $data['post_type'] ?? 'meta-box';
		if ( ! LocalJson::is_supported( $type ) ) {
			return null;
		}

		return [
			'file'    => $file,
			'raw'     => $raw,
			'data'    => $data,
			'minimal' => $unparser->to_minimal_format(),
		];
	}

	/**
	 * Best-effort type from raw JSON without unparsing.
	 */
	private static function detect_post_type_hint( array $raw ): string {
		if ( ! empty( $raw['post_type'] ) && is_string( $raw['post_type'] ) ) {
			return $raw['post_type'];
		}

		$schema = $raw['$schema'] ?? '';
		if ( ! is_string( $schema ) || $schema === '' ) {
			return '';
		}

		$found = array_search( $schema, MetaBox::SCHEMAS, true );

		return false !== $found ? $found : '';
	}

	/**
	 * Build compare items: Local JSON first, then overlay database posts.
	 */
	private static function query_json( string $post_type ): array {
		$items = self::items_from_files( $post_type );

		foreach ( self::get_meta_boxes( [ 'post_type' => $post_type ] ) as $meta_box ) {
			if ( empty( $meta_box['id'] ) ) {
				continue;
			}

			$id      = $meta_box['id'];
			$post_id = $meta_box['post_id'];
			unset( $meta_box['post_id'], $meta_box['post_type'] );

			if ( ! isset( $items[ $id ] ) ) {
				$file = self::get_future_path( $id );

				$items[ $id ] = [
					'file'            => $file,
					'is_writable'     => Path::is_future_path_writable( dirname( $file ) ),
					'id'              => $id,
					'is_newer'        => -1,
					'diff'            => self::diff( $meta_box, null ),
					'local'           => null,
					'local_minimized' => null,
					'post_id'         => $post_id,
					'post_type'       => $post_type,
					'remote'          => $meta_box,
				];
				continue;
			}

			$local_modified = $items[ $id ]['local_minimized']['modified'] ?? 0;

			$items[ $id ] = array_merge( $items[ $id ], [
				'id'        => $id,
				'is_newer'  => version_compare( $local_modified, $meta_box['modified'] ?? 0 ),
				'remote'    => $meta_box,
				'diff'      => self::diff( $meta_box, $items[ $id ]['local'] ),
				'post_id'   => $post_id,
				'post_type' => $post_type,
			] );
		}

		return $items;
	}

	/**
	 * Build compare/sync items from Local JSON files for a post type.
	 *
	 * @return array<string, array> Items keyed by JSON id.
	 */
	private static function items_from_files( string $post_type ): array {
		$items = [];

		foreach ( self::get_unparsed( $post_type ) as $item ) {
			// Hide from Sync UI; registers still load these via get_unparsed().
			if ( ! empty( $item['raw']['private'] ) ) {
				continue;
			}

			$minimal = $item['minimal'];
			if ( empty( $minimal['id'] ) ) {
				continue;
			}

			$id = $minimal['id'];

			$items[ $id ] = [
				'file'            => $item['file'],
				'local'           => $item['raw'],
				'local_minimized' => $minimal,
				'is_newer'        => 1,
				'post_id'         => null,
				'post_type'       => $item['data']['post_type'] ?? 'meta-box',
				'id'              => $id,
				'remote'          => null,
				'diff'            => self::diff( null, $item['raw'] ),
				'is_writable'     => is_writable( $item['file'] ),
			];
		}

		return $items;
	}

	/**
	 * HTML diff between database and Local JSON payloads.
	 *
	 * @param array|null $left  Database (remote) side.
	 * @param array|null $right Local JSON side.
	 */
	private static function diff( ?array $left, ?array $right ): string {
		return wp_text_diff(
			self::encode_for_diff( $left ),
			self::encode_for_diff( $right ),
			[ 'show_split_view' => true ]
		);
	}

	private static function encode_for_diff( ?array $data ): string {
		return empty( $data ) ? '' : wp_json_encode( $data, self::DIFF_JSON_FLAGS );
	}

	private static function filter_items( array $items, array $params ): array {
		if ( isset( $params['id'] ) ) {
			$items = array_filter( $items, function ( $item ) use ( $params ) {
				// Loose compare: REST/URL params are strings; item post_id/is_newer are ints.
				return $item['id'] == $params['id']; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			} );
		}

		foreach ( [ 'is_newer', 'post_id', 'file', 'post_type' ] as $key ) {
			if ( ! isset( $params[ $key ] ) ) {
				continue;
			}

			$items = array_filter( $items, function ( $item ) use ( $key, $params ) {
				// phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- See id filter above.
				return isset( $item[ $key ] ) && $item[ $key ] == $params[ $key ];
			} );
		}

		return $items;
	}

	/**
	 * Meta keys that hold the registered object for this post type.
	 *
	 * @return string[]
	 */
	private static function get_related_meta_keys( string $post_type ): array {
		$meta_keys = [
			'meta-box'         => [ 'meta_box' ],
			'mb-relationship'  => [ 'relationship' ],
			'mb-settings-page' => [ 'settings_page' ],
			'mb-model'         => [ 'model' ],
		];

		return $meta_keys[ $post_type ] ?? [];
	}

	public static function get_meta_boxes( array $query_params = [], $format = 'minimal' ): array {
		$defaults = [
			'post_type'              => 'meta-box',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		];

		$query_params = wp_parse_args( $query_params, $defaults );
		$query        = new WP_Query( $query_params );

		$meta_boxes = [];
		foreach ( $query->posts as $post ) {
			$post_data = (array) $post;

			// Drafts don't have post_name so we skip them
			if ( empty( $post_data['post_name'] ) ) {
				continue;
			}

			foreach ( self::get_related_meta_keys( $query_params['post_type'] ) as $meta_key ) {
				$post_data[ $meta_key ] = get_post_meta( $post->ID, $meta_key, true ) ?: [];
			}

			$post_data['settings'] = (array) get_post_meta( $post->ID, 'settings', true );

			$unparser = new MetaBox( $post_data );
			$unparser->unparse();
			$post_data = $format === 'minimal' ? $unparser->to_minimal_format() : $unparser->get_settings();

			// Extra post_id, post_type for filtering, check this line carefully if you want to change it
			$post_data['post_id']   = $post->ID;
			$post_data['post_type'] = $query_params['post_type'];

			$meta_boxes[ $post->ID ] = $post_data;
		}

		return $meta_boxes;
	}

	/**
	 * Absolute paths of all Local JSON files.
	 *
	 * @return string[]
	 */
	public static function get_files(): array {
		$all_files = [];
		foreach ( self::get_paths() as $path ) {
			$all_files = array_merge( $all_files, glob( "$path/*.json" ) );
		}

		return apply_filters( 'mbb_json_files', $all_files );
	}

	/**
	 * Writable directories that hold Local JSON files.
	 *
	 * @return string[]
	 */
	public static function get_paths(): array {
		static $paths = [];

		if ( ! empty( $paths ) ) {
			return $paths;
		}

		$theme_path = get_stylesheet_directory();
		if ( file_exists( "$theme_path/mb-json" ) ) {
			$paths[] = "$theme_path/mb-json";
		}

		$paths = apply_filters( 'mb_json_paths', $paths );

		if ( is_string( $paths ) ) {
			$paths = [ $paths ];
		}

		$paths = array_filter( $paths, 'is_writable' );

		return $paths;
	}

	public static function get_future_path( string $meta_box_id ): string {
		if ( ! LocalJson::is_enabled() ) {
			return '';
		}

		return self::get_paths()[0] . "/$meta_box_id.json";
	}
}
