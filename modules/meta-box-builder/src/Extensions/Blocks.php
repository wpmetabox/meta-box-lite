<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;
use MBBParser\Parsers\Settings;

class Blocks {
	public function __construct() {
		if ( ! Data::is_extension_active( 'mb-blocks' ) ) {
			return;
		}
		add_filter( 'mbb_settings_controls', [ $this, 'add_settings_controls' ] );
		add_filter( 'mbb_app_data', [ $this, 'add_app_data' ] );
		add_action( 'mbb_after_save', [ $this, 'generate_block_json' ], 10, 3 );
		add_action( 'init', [ $this, 'register_blocks' ] );

		// Add filters to alter the block metadata.
		add_filter( 'mbb_save_settings', [ $this, 'alter_settings' ], 10, 2 );
		add_filter( 'mbb_save_fields', [ $this, 'alter_fields' ], 10, 2 );
		add_filter( 'mbb_save_submitted_data', [ $this, 'alter_submitted_data' ], 10, 2 );
		add_filter( 'wp_insert_post_data', [ $this, 'alter_post_data' ], 10, 2 );
	}

	public function alter_post_data( $post_data, $post_id ) {
		//phpcs:ignore
		if ( ! isset( $_POST['override_block_json'] ) || ! $_POST['override_block_json'] ) {
			return $post_data;
		}
		$request    = rwmb_request();
		$block_json = self::get_block_metadata( $request );

		$post_data['post_title'] = $block_json['title'];

		return $post_data;
	}

	public static function get_block_metadata( $request ) {
		$meta_box_settings = $request->post( 'settings' );

		if ( ! isset( $meta_box_settings['block_json'] ) || ! isset( $meta_box_settings['block_json']['path'] ) ) {
			return;
		}

		$path   = $meta_box_settings['block_json']['path'];
		$parser = new Settings();
		$path   = $parser->replace_variables( $path );

		$block_id = $request->post( 'post_name' );
		// phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent
		$path_to_block_json = $path . '/' . $block_id . '/block.json';

		if ( ! file_exists( $path_to_block_json ) || ! is_readable( $path_to_block_json ) ) {
			return;
		}

		$block_json = json_decode( file_get_contents( $path_to_block_json ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		$block_json['version'] = $block_json['version'] ?? 'v0';
		$block_json['version'] = (int) str_replace( 'v', '', $block_json['version'] );
		$block_json['version'] = max( $block_json['version'], filemtime( $path_to_block_json ) );

		return $block_json;
	}

	public function alter_settings( ?array $settings, $request ) {
		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		if ( ! $this->has_override_block_json( $request ) ) {
			return $settings;
		}

		$block_json                        = self::get_block_metadata( $request );
		$settings['description']           = $block_json['description'];
		$icon_type                         = str_contains( $block_json['icon'], '<svg' ) ? 'svg' : 'dashicons';
		$settings['icon_type']             = $icon_type;
		$settings['icon']                  = $icon_type === 'dashicons' ? $block_json['icon'] : '';
		$settings['icon_svg']              = $icon_type === 'svg' ? $block_json['icon'] : '';
		$settings['category']              = $block_json['category'];
		$settings['keywords']              = $block_json['keywords'];
		$settings['block_json']['version'] = $block_json['version'];

		return $settings;
	}

	public function alter_fields( ?array $fields, $request ) {
		if ( ! is_array( $fields ) || ! $this->has_override_block_json( $request ) ) {
			return $fields;
		}

		$block_json = self::get_block_metadata( $request );

		// Loop through the attributes and get array of fields
		$attributes = $block_json['attributes'];

		foreach ( $attributes as $name => $value ) {
			if ( is_array( $value ) && isset( $value['meta-box-field'] ) ) {
				$field                   = $value['meta-box-field'];
				$field['id']             = $name;
				$fields[ $field['_id'] ] = $field;
			}
		}

		return $fields;
	}

	public function alter_submitted_data( array $post, $request ) {
		if ( ! $this->has_override_block_json( $request ) ) {
			return $post;
		}

		$block_json = self::get_block_metadata( $request );

		$post['fields']   = $this->alter_fields( $post['fields'], $request );
		$post['settings'] = $this->alter_settings( $post['settings'], $request );

		return $post;
	}

	private function has_override_block_json( $request ) {
		$override_block_json = $request->post( 'override_block_json' );

		return ! empty( $override_block_json );
	}

	public function register_blocks() {
		$query = new \WP_Query( [
			'post_type'              => 'meta-box',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'fields'                 => 'ids',
			'update_post_term_cache' => false,
		] );

		foreach ( $query->posts as $post_id ) {
			$meta_box = get_post_meta( $post_id, 'meta_box', true );

			if ( empty( $meta_box ) ) {
				continue;
			}

			// Bail if this is not a block.
			if ( empty( $meta_box['type'] ) || 'block' !== $meta_box['type'] ) {
				continue;
			}

			// Bail if block path is empty.
			if ( empty( $meta_box['block_json'] ) || empty( $meta_box['block_json']['path'] ) ) {
				continue;
			}

			if ( ! isset( $meta_box['block_json']['enable'] )
				|| ! $meta_box['block_json']['enable']
				|| ! file_exists( $meta_box['block_json']['path'] )
				|| isset( $meta_box['function_name'] )
				|| isset( $meta_box['render_template'] )
			) {
				continue;
			}

			// Now we register the block with the provided path
			register_block_type( trailingslashit( $meta_box['block_json']['path'] ) . $meta_box['id'] );
		}
	}

	public function add_settings_controls( $controls ) {
		$controls[12] = Control::Block( 'block' );
		return $controls;
	}

	public function add_app_data( array $data ) {
		$data['blockCategories'] = wp_list_pluck( get_block_categories( get_post() ), 'title', 'slug' );
		$data['settings']        = is_array( $data['settings'] ) ? $data['settings'] : [];

		$block_json_settings = $data['settings']['block_json'] ?? [
			'enable'  => true,
			'version' => 'v' . time(),
			'path'    => '{{ theme.path }}/blocks',
		];

		$data['settings']['block_json'] = $block_json_settings;
		$data['views']                  = Data::get_views();
		$data['viewAddUrl']             = admin_url( 'post-new.php?post_type=mb-views' );
		$data['viewEditUrl']            = admin_url( 'post.php?post=' );

		return $data;
	}

	public function generate_block_json( $parser, $post_id, $raw_data ) {
		// Don't generate block JSON if we are syncing from block.json
		if ( isset( $raw_data['override_block_json'] ) && $raw_data['override_block_json'] ) {
			return;
		}

		$settings = $parser->get_settings();

		// Bail if this is not a block.
		if ( ! isset( $settings['type'] ) || $settings['type'] !== 'block' ) {
			return;
		}

		// Bail if block path is empty.
		if ( empty( $settings['block_json'] ) || ! $settings['block_json']['enable'] ) {
			return;
		}

		$this->generate_block_package( $settings, $post_id, $raw_data );
	}

	private function generate_block_metadata( array $settings, array $raw_data ): array {
		$block_id = sanitize_title( $settings['title'] );

		$metadata = [
			'$schema'     => 'https://schemas.wp.org/trunk/block.json',
			'apiVersion'  => 3,
			'version'     => 'v' . time(),
			'name'        => "meta-box/{$block_id}",
			'title'       => $settings['title'] ?? '',
			'description' => $settings['description'] ?? '',
			'category'    => $settings['category'] ?? 'common',
			'icon'        => $settings['icon'] ?? $settings['icon_svg'] ?? 'admin-generic',
			'keywords'    => $settings['keywords'] ?? [],
			'supports'    => [
				'html'   => false,
				'anchor' => false,
				'align'  => true,
			],
		];

		if ( ! empty( $settings['render_callback'] ) && str_starts_with( $settings['render_callback'], 'view:' ) ) {
			$metadata['render'] = $settings['render_callback'];
		}

		// Add fields to block metadata attributes.
		$attributes = [];
		if ( isset( $raw_data['fields'] ) && is_array( $raw_data['fields'] ) ) {
			$attributes = $this->generate_block_attributes( $raw_data['fields'] );
		}

		$align = array_filter( $settings['supports']['align'] );
		// Alignments
		if ( ! empty( $align ) ) {
			$metadata['supports']['align'] = $align;
			$attributes['align']           = [
				'type' => 'string',
			];
		}

		$metadata['attributes'] = ! empty( $attributes ) ? $attributes : new \stdClass();

		return $metadata;
	}

	/**
	 * Generate block metadata attribute
	 *
	 * If field is multiple or clone, then field type is array.
	 * If field type is number, then field type is number.
	 * Otherwise, field type is string.
	 *
	 * @todo: Add support for other field types. For example, enum.
	 */
	private function generate_block_attributes( ?array $fields ) {
		if ( ! is_array( $fields ) ) {
			return [];
		}

		$attributes = [];

		foreach ( $fields as $field ) {
			$id       = $field['id'] ?? $field['_id'] ?? null;
			$type_std = $this->get_field_type_and_default_value( $field );

			if ( is_null( $type_std ) ) {
				continue;
			}

			[ $type, $std ] = $type_std;

			$attributes[ $id ] = [
				'type'           => $type,
				'meta-box-field' => $field,
			];

			if ( $std ) {
				$attributes[ $id ]['default'] = $std;
			}
		}

		return $attributes;
	}

	private function get_field_type_and_default_value( $field ) {
		$type = 'string';
		$std  = $field['std'] ?? null;

		// These fields returns array
		$array_fields = [
			'group',
			'checkbox_list',
			'file_advanced',
			'autocomplete',
			'file_upload',
			'file',
			'image',
			'image_advanced',
			'image_upload',
			'key_value',
		];

		$field['id'] = $field['id'] ?? $field['_id'] ?? null;

		if ( ! isset( $field['type'] ) || ! isset( $field['id'] ) ) {
			return;
		}

		if ( in_array( $field['type'], [ 'number', 'slider', 'range' ] ) ) {
			$type = 'number';
			$std  = is_numeric( $field['std'] ) ? $field['std'] : 0;
		}

		if ( in_array( $field['type'], [ 'checkbox', 'switch' ] ) ) {
			$type = 'boolean';
			$std  = isset( $field['std'] ) ? (bool) $field['std'] : false;
		}

		if ( in_array( $field['type'], [ 'single_image', 'file_input', 'user', 'post' ] ) ) {
			$type = 'object';
			$std  = new \stdClass();
		}

		$is_multiple = ( isset( $field['multiple'] ) && $field['multiple'] )
			|| ( isset( $field['type'] ) && in_array( $field['type'], $array_fields ) )
			|| ( isset( $field['field_type'] ) && in_array( $field['field_type'], [ 'select_tree', 'checkbox_tree', 'checkbox_list', 'checkbox_tree' ] ) );

		$is_cloneable = $field['clone'] ?? false;

		if ( $is_multiple || $is_cloneable ) {
			$type = 'array';
			$std  = ! empty( $field['std'] ) && is_array( $field['std'] ) ? $field['std'] : [];
		}

		return [ $type, $std ];
	}

	private function generate_block_package( array $settings, $post_id, $raw_data ): void {
		$block_id          = $settings['id'];
		$block_path        = trailingslashit( $settings['block_json']['path'] ) . $block_id;
		$parent_block_path = dirname( $block_path );

		if ( ! self::is_future_path_writable( $parent_block_path ) ) {
			// Return an error message.
			$data = get_post_meta( $post_id, 'data', true );

			if ( ! is_array( $data ) ) {
				$data = [];
			}

			$data['block_path_error'] = __( 'Block path is not writable. Please check the folder permission.', 'meta-box-builder' );

			update_post_meta( $post_id, 'data', $data );

			return;
		}

		if ( ! file_exists( $block_path ) ) {
			wp_mkdir_p( $block_path );
		}

		$new_metadata = $this->generate_block_metadata( $settings, $raw_data );

		// Compare old and new block metadata, and save the new one if it's newer.
		$block_json_path = $block_path . '/block.json';

		$is_newer = false;

		if ( ! file_exists( $block_json_path ) ) {
			$is_newer = true;
		} else {
			$current_metadata = json_decode( file_get_contents( $block_json_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			foreach ( $new_metadata as $key => $value ) {
				if ( $key === 'version' ) {
					continue;
				}

				if ( ! isset( $current_metadata[ $key ] ) || $current_metadata[ $key ] !== $value ) {
					$is_newer = true;
					break;
				}
			}
		}

		if ( ! $is_newer ) {
			return;
		}

		// Save the new version back to the post meta.
		$settings                          = get_post_meta( $post_id, 'settings', true );
		$settings['block_json']['version'] = $new_metadata['version'];
		update_post_meta( $post_id, 'settings', $settings );

		// phpcs:disable
		$new_metadata = json_encode( $new_metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		file_put_contents( $block_json_path, $new_metadata );
		chmod( $block_json_path, 0664 );
		// phpcs:enable
	}

	/**
	 * Check if the intended path is writable.
	 *
	 * Because is_writable() only checks the existing path, and returns false if the path doesn't exist,
	 * this method checks if we can create the path, also do the additional security check to make sure the path is inside
	 * the WordPress installation.
	 */
	public static function is_future_path_writable( string $path ): bool {
		$path = trailingslashit( $path );

		// For security, we only allow the path inside the current WordPress installation.
		if ( ! str_starts_with( $path, wp_normalize_path( ABSPATH ) ) ) {
			return false;
		}

		$paths = explode( '/', $path );

		// Traverse from the leaf to the root to get the first existing directory
		// and check if it's writable
		while ( count( $paths ) > 1 ) {
			array_pop( $paths );
			$path_str = implode( '/', $paths );

			if ( file_exists( $path_str ) ) {
				break;
			}
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
		return is_dir( $path_str ) && is_writable( $path_str );
	}
}
