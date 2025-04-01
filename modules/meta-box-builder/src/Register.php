<?php
namespace MBB;

use MetaBox\Support\Arr;

class Register {
	private $meta_box_post_ids = [];

	public function __construct() {
		add_filter( 'rwmb_meta_boxes', [ $this, 'register_meta_box' ] );
	}

	public function register_meta_box( $meta_boxes ): array {
		$mbs = LocalJson::is_enabled() ? $this->get_json_meta_boxes() : $this->get_database_meta_boxes();

		foreach ( $mbs as $meta_box ) {
			$this->transform_for_block( $meta_box['meta_box'] );
			$this->create_custom_table( $meta_box );

			if ( empty( $meta_box['meta_box'] ) ) {
				continue;
			}

			$settings = $meta_box['settings'] ?? [];

			$object_type = Arr::get( $settings, 'object_type' );

			if ( $object_type === 'post' ) {
				$this->meta_box_post_ids[ $meta_box['meta_box']['id'] ] = $meta_box['meta_box']['id'];
			}

			// Allow WPML to modify the meta box to use translations. JSON meta boxes might not have post_title and post_name.
			if ( isset( $meta_box['post_title'] ) && isset( $meta_box['post_name'] ) ) {
				$post_object = (object) [
					'post_title' => $meta_box['post_title'],
					'post_name'  => $meta_box['post_name'],
				];

				$meta_box['meta_box'] = apply_filters( 'mbb_meta_box', $meta_box['meta_box'], $post_object );
			}

			$meta_boxes[] = $meta_box['meta_box'];
		}

		if ( ! empty( $this->meta_box_post_ids ) && is_admin() ) {
			add_action( 'rwmb_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		}

		return $meta_boxes;
	}

	private function get_json_meta_boxes(): array {
		$meta_boxes = [];

		$files = JsonService::get_files();
		foreach ( $files as $file ) {
			[ $data, $error ] = LocalJson::read_file( $file );

			if ( $data === null || $error !== null ) {
				continue;
			}

			$json     = json_decode( $data, true );
			$unparser = new \MBBParser\Unparsers\MetaBox( $json );
			$unparser->unparse();
			$json     = $unparser->get_settings();
			$meta_box = $json;

			if ( empty( $meta_box ) ) {
				continue;
			}

			$meta_boxes[] = $meta_box;
		}

		return $meta_boxes;
	}

	public function get_database_meta_boxes(): array {
		$meta_boxes = JsonService::get_meta_boxes( [
			'post_status' => 'publish',
		], 'full' );

		return $meta_boxes;
	}

	private function transform_for_block( &$meta_box ) {
		if ( ! Helpers\Data::is_extension_active( 'mb-blocks' ) || empty( $meta_box['type'] ) || 'block' !== $meta_box['type'] ) {
			return;
		}

		if ( empty( $meta_box['render_code'] ) ) {
			return;
		}

		$meta_box['render_callback'] = function ( $attributes, $is_preview = false, $post_id = null ) use ( $meta_box ) {
			$data               = $attributes;
			$data['is_preview'] = $is_preview;
			$data['post_id']    = $post_id;

			// Get all fields data.
			$fields = array_filter( $meta_box['fields'], [ $this, 'has_value' ] );
			foreach ( $fields as $field ) {
				$data[ $field['id'] ] = 'group' === $field['type'] ? mb_get_block_field( $field['id'], [] ) : mb_the_block_field( $field['id'], [], false );
			}

			$loader = new \eLightUp\Twig\Loader\ArrayLoader( [
				'block' => '{% autoescape false %}' . $meta_box['render_code'] . '{% endautoescape %}',
			] );
			$twig   = new \eLightUp\Twig\Environment( $loader );

			// Proxy for all PHP/WordPress functions.
			$data['mb'] = new TwigProxy();

			echo $twig->render( 'block', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		};
	}

	private function create_custom_table( $meta_box ): void {
		if ( ! Helpers\Data::is_extension_active( 'mb-custom-table' ) || empty( $meta_box['meta_box']['table'] ) ) {
			return;
		}

		// Get full custom table settings from both meta box settings and JavaScript data.
		$custom_table_settings = $meta_box['meta_box']['custom_table'] ?? $meta_box['settings']['custom_table'] ?? [];

		if ( empty( $custom_table_settings ) || ! is_array( $custom_table_settings ) ) {
			return;
		}

		if ( ! Arr::get( $custom_table_settings, 'create' ) ) {
			return;
		}

		$meta_box = $meta_box['meta_box'];
		$columns = [];
		$fields  = array_filter( $meta_box['fields'], [ $this, 'has_value' ] );
		foreach ( $fields as $field ) {
			$columns[ $field['id'] ] = 'TEXT';
		}

		$data      = [
			'table'   => $meta_box['table'],
			'columns' => $columns,
		];
		$cache_key = 'mb_create_table_' . md5( wp_json_encode( $data ) );
		if ( get_transient( $cache_key ) !== false ) {
			return;
		}

		\MB_Custom_Table_API::create( $meta_box['table'], $columns );
		set_transient( $cache_key, 1, MONTH_IN_SECONDS );
	}

	public function enqueue_assets(): void {
		wp_enqueue_style( 'mbb-post', MBB_URL . 'assets/css/post.css', [], MBB_VER );
		wp_enqueue_script( 'mbb-post', MBB_URL . 'assets/js/post.js', [], MBB_VER, true );
		\RWMB_Helpers_Field::localize_script_once( 'mbb-post', 'MBB', [
			'meta_box_post_ids' => $this->meta_box_post_ids,
			'base_url'          => get_rest_url( null, 'mbb/redirection-url' ),
			'title'             => __( 'Edit the field group settings', 'meta-box-builder' ),
		] );
	}

	private function has_value( $field ): bool {
		return ! empty( $field['id'] ) && ! in_array( $field['type'], [ 'heading', 'divider', 'button', 'custom_html', 'tab' ], true );
	}
}
