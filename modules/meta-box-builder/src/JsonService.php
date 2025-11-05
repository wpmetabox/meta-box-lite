<?php
namespace MBB;

use MBB\Helpers\Path;

class JsonService {
	/**
	 * Get data from JSON file and format it in a verbose format to use everywhere possible:
	 * - Compare
	 * - Sync to database
	 *
	 * Each JSON file after formatting will contains the following data:
	 * [
	 *      'file'            => string,
	 *      'local'           => array,
	 *      'local_minimized' => array,
	 *      'is_newer'        => number<-1|0|1>,
	 *      'post_id'         => null|int,
	 *      'post_type'       => string<'meta-box'>,
	 *      'id'              => string,
	 *      'remote'          => null|array<meta box array>,
	 *      'diff'            => string,
	 *      'is_writable'     => bool,
	 * ]
	 *
	 * @param array $params
	 * @return array[]
	 */
	public static function get_json( array $params = [] ): array {
		static $items = null;

		if ( $items === null ) {
			$items = self::query_json( $params );
		}

		$filter_items = self::filter_items( $items, $params );

		return $filter_items;
	}

	private static function query_json( array $params ): array {
		$files = self::get_files();

		// key by meta box id
		$items = [];
		foreach ( $files as $file ) {
			$raw_json = LocalJson::read_file( $file );
			if ( empty( $raw_json ) ) {
				continue;
			}

			$private = $raw_json['private'] ?? false;
			if ( $private ) {
				continue;
			}

			$unparser = new \MBBParser\Unparsers\MetaBox( $raw_json );
			$unparser->unparse();
			$json            = $unparser->get_settings();
			$local_minimized = $unparser->to_minimal_format();

			// ID is required so we can compare with the post ID
			if ( ! isset( $local_minimized['id'] ) ) {
				continue;
			}

			$diff = wp_text_diff( '', wp_json_encode( $raw_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ), [
				'show_split_view' => true,
			] );

			$is_writeable = is_writable( $file );

			$items[ $local_minimized['id'] ] = [
				'file'            => $file,
				'local'           => $raw_json,
				'local_minimized' => $local_minimized,
				'is_newer'        => 1,
				'post_id'         => null,
				'post_type'       => $json['post_type'] ?? 'meta-box',
				'id'              => $local_minimized['id'],
				'remote'          => null,
				'diff'            => $diff,
				'is_writable'     => $is_writeable,
			];
		}

		$post_type = $params['post_type'] ?? 'meta-box';
		if ( isset( $params['post_id'] ) ) {
			$params['post__in'] = [ $params['post_id'] ];
		}

		$meta_boxes = self::get_meta_boxes( $params );

		foreach ( $meta_boxes as $meta_box ) {
			if ( ! isset( $meta_box['id'] ) ) {
				continue;
			}

			$id        = $meta_box['id'];
			$post_id   = $meta_box['post_id'];
			$post_type = $meta_box['post_type'];

			// Remove post_id, post_type to avoid diff
			unset( $meta_box['post_id'] );
			unset( $meta_box['post_type'] );

			// No file found
			if ( ! isset( $items[ $id ] ) ) {
				$left = empty( $meta_box ) ? '' : wp_json_encode( $meta_box, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

				$diff = wp_text_diff( $left, '', [
					'show_split_view' => true,
				] );

				$file         = self::get_future_path( $id );
				$folder       = dirname( $file );
				$is_writeable = Path::is_future_path_writable( $folder );

				$items[ $id ] = [
					'file'            => $file,
					'is_writable'     => $is_writeable,
					'id'              => $id,
					'is_newer'        => -1,
					'diff'            => $diff,
					'local'           => null,
					'local_minimized' => null,
					'post_id'         => $post_id,
					'post_type'       => $post_type,
					'remote'          => $meta_box,
				];

				continue;
			}

			$local_modified = $items[ $id ]['local_minimized']['modified'] ?? 0;
			$is_newer       = version_compare( $local_modified, $meta_box['modified'] ?? 0 );

			$left = empty( $meta_box ) ? '' : wp_json_encode( $meta_box, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

			$diff = wp_text_diff( $left, wp_json_encode( $items[ $id ]['local'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ), [
				'show_split_view' => true,
			] );

			$items[ $id ] = array_merge( $items[ $id ], [
				'id'        => $id,
				'is_newer'  => $is_newer,
				'remote'    => $meta_box,
				'diff'      => $diff,
				'post_id'   => $post_id,
				'post_type' => $post_type,
			] );
		}

		return $items;
	}

	private static function filter_items( array $items, array $params ): array {
		// Filter by params
		if ( isset( $params['id'] ) ) {
			$items = array_filter( $items, function ( $item ) use ( $params ) {
				return $item['id'] == $params['id'];
			} );
		}

		foreach ( [ 'is_newer', 'post_id', 'file' ] as $key ) {
			if ( ! isset( $params[ $key ] ) ) {
				continue;
			}

			$items = array_filter( $items, function ( $item ) use ( $key, $params ) {
				return isset( $item[ $key ] ) && $item[ $key ] == $params[ $key ];
			} );
		}

		return $items;
	}

	/**
	 * Bare minimum keys needed in the json file
	 *
	 * @param string $post_type
	 * @return string[]
	 */
	private static function get_related_meta_keys( string $post_type ): array {
		$meta_keys = [
			'meta-box'         => [ 'meta_box' ],
			'mb-relationship'  => [ 'relationship' ],
			'mb-settings-page' => [ 'settings_page' ],
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
		$query        = new \WP_Query( $query_params );

		$meta_boxes = [];
		foreach ( $query->posts as $post ) {
			$post_data = (array) $post;

			// Drafts don't have post_name so we skip them
			if ( empty( $post_data['post_name'] ) ) {
				continue;
			}

			$meta_keys = self::get_related_meta_keys( $query_params['post_type'] );

			foreach ( $meta_keys as $meta_key ) {
				$main_meta              = get_post_meta( $post->ID, $meta_key, true ) ?: [];
				$post_data[ $meta_key ] = $main_meta;
			}

			$settings              = get_post_meta( $post->ID, 'settings', true );
			$post_data['settings'] = (array) $settings;

			$unparser = new \MBBParser\Unparsers\MetaBox( $post_data );
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
	 * Get all meta box .json files
	 *
	 * @return string[]
	 */
	public static function get_files(): array {
		$paths     = self::get_paths();
		$all_files = [];

		foreach ( $paths as $path ) {
			$all_files = array_merge( $all_files, glob( "$path/*.json" ) );
		}

		$all_files = apply_filters( 'mbb_json_files', $all_files );

		return $all_files;
	}

	/**
	 * Get all paths to search for .json files
	 *
	 * @return string[]
	 */
	public static function get_paths(): array {
		// Cache paths to avoid multiple calls to this function.
		static $paths = [];

		if ( ! empty( $paths ) ) {
			return $paths;
		}

		$theme_path = get_stylesheet_directory();

		if ( file_exists( "$theme_path/mb-json" ) ) {
			$paths[] = "$theme_path/mb-json";
		}

		$paths = apply_filters( 'mb_json_paths', $paths );

		// Allow developers to return a single path.
		if ( is_string( $paths ) ) {
			$paths = [ $paths ];
		}

		// Remove unwritable paths
		$paths = array_filter( $paths, function ( $path ) {
			return is_writable( $path );
		} );

		return $paths;
	}

	/**
	 * Get the path to the future .json file
	 *
	 * @param string $meta_box_id
	 *
	 * @return string
	 */
	public static function get_future_path( string $meta_box_id ): string {
		if ( ! LocalJson::is_enabled() ) {
			return '';
		}

		return self::get_paths()[0] . "/$meta_box_id.json";
	}
}
