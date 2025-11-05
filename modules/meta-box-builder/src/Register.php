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
			if ( empty( $meta_box['meta_box'] ) ) {
				continue;
			}

			// Use do_action_ref_array() to pass the reference to the meta box, to be able to modify it.
			// See Extensions\Blocks\CodeToCallbackTransformer.
			do_action_ref_array( 'mbb_before_register_meta_box', [ &$meta_box ] );

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
			$json = LocalJson::read_file( $file );
			if ( empty( $json ) ) {
				continue;
			}

			$unparser = new \MBBParser\Unparsers\MetaBox( $json );
			$unparser->unparse();
			$meta_box = $unparser->get_settings();

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
