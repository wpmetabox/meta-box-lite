<?php
namespace MBB\Extensions\Blocks\Json;

use MBBParser\Parsers\Settings;
use MBBParser\Parsers\MetaBox as MetaBoxParser;
use WP_REST_Request;
use WP_REST_Server;

class Overrider {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	public function register_rest_routes(): void {
		register_rest_route( 'mbb', 'blocks/json/override', [
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => [ $this, 'override' ],
			'permission_callback' => function (): bool {
				return current_user_can( 'edit_posts' );
			},
		] );
	}

	public function override( WP_REST_Request $request ): array {
		$post_id = $request->get_param( 'post_id' );
		if ( ! $post_id ) {
			return [
				'success' => false,
				'message' => __( 'Invalid post ID', 'meta-box-builder' ),
			];
		}

		// Get block metadata from block.json
		$block_json = $this->get_block_metadata( $request );
		if ( empty( $block_json ) ) {
			return [
				'success' => false,
				'message' => __( 'Could not read block.json file', 'meta-box-builder' ),
			];
		}

		$post_title = $block_json['title'] ?? '';
		$post_name  = str_replace( 'meta-box/', '', $block_json['name'] ?? '' );

		// Update post title and slug.
		wp_update_post( [
			'ID'         => $post_id,
			'post_title' => $post_title,
			'post_name'  => $post_name,
		] );

		// Update settings from block.json
		$settings = get_post_meta( $post_id, 'settings', true );
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}
		$settings['description']           = $block_json['description'] ?? '';
		$icon_type                         = str_contains( $block_json['icon'], '<svg' ) ? 'svg' : 'dashicons';
		$settings['icon_type']             = $icon_type;
		$settings['icon']                  = $icon_type === 'dashicons' ? $block_json['icon'] : '';
		$settings['icon_svg']              = $icon_type === 'svg' ? $block_json['icon'] : '';
		$settings['category']              = $block_json['category'] ?? '';
		$settings['keywords']              = $block_json['keywords'] ?? [];
		$settings['block_json']['version'] = $block_json['version'] ?? 'v' . time();
		update_post_meta( $post_id, 'settings', $settings );

		// Update fields from block.json attributes
		$fields = get_post_meta( $post_id, 'fields', true );
		if ( ! is_array( $fields ) ) {
			$fields = [];
		}
		$attributes = isset( $block_json['attributes'] ) && is_array( $block_json['attributes'] ) ? $block_json['attributes'] : [];
		$new_fields = [];
		foreach ( $attributes as $name => $value ) {
			if ( ! is_array( $value ) || ! isset( $value['meta-box-field'] ) ) {
				continue;
			}

			$field       = $value['meta-box-field'];
			$field['id'] = $name;
			$new_fields[] = $field;
		}

		$this->merge_list( $fields, $new_fields );
		update_post_meta( $post_id, 'fields', $fields );

		// Save parsed data for PHP
		$submitted_data = [
			'fields'     => $fields,
			'settings'   => $settings,
			'post_title' => $post_title,
			'post_name'  => $post_name,
		];

		$parser = new MetaBoxParser( $submitted_data );
		$parser->parse();
		update_post_meta( $post_id, 'meta_box', $parser->get_settings() );

		// Trigger after save action
		do_action( 'mbb_after_save', $parser, $post_id, $submitted_data );

		return [
			'success' => true,
			'message' => __( 'Block settings overridden successfully', 'meta-box-builder' ),
		];
	}

	private function merge_list( array &$list_one, array $list_two ): void {
		foreach ( $list_two as $item_two ) {
			$found = false;
			foreach ( $list_one as &$item_one ) {
				if ( $item_one['_id'] !== $item_two['_id'] ) {
					continue;
				}

				$item_one = $item_two;
				$found    = true;

				// Do the same for nested fields.
				if ( isset( $item_two['fields'] ) ) {
					$item_one['fields'] = $item_one['fields'] ?? [];
					$this->merge_list( $item_one['fields'], $item_two['fields'] );
				}
				break;
			}

			if ( ! $found ) {
				$list_one[] = $item_two;
			}
		}
	}

	private function get_block_metadata( WP_REST_Request $request ): array {
		$path = $request->get_param( 'path' );
		if ( ! $path ) {
			return [];
		}

		$parser = new Settings();
		$path   = $parser->replace_variables( $path );

		$block_id = $request->get_param( 'post_name' );
		// phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent
		$path = "$path/$block_id/block.json";

		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			return [];
		}

		$block_json = json_decode( file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		$block_json['version'] = $block_json['version'] ?? 'v0';
		$block_json['version'] = (int) str_replace( 'v', '', $block_json['version'] );
		$block_json['version'] = max( $block_json['version'], filemtime( $path ) );

		return $block_json;
	}
}