<?php
namespace MBB;

abstract class BaseEditPage {
	protected $post_type;
	protected $slug_meta_box_title;

	public function __construct( $post_type, $slug_meta_box_title ) {
		$this->post_type           = $post_type;
		$this->slug_meta_box_title = $slug_meta_box_title;

		add_action( 'edit_form_after_title', [ $this, 'render' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_wrapper' ] );
		add_action( "save_post_$post_type", [ $this, 'save_wrapper' ], 10, 2 );

		add_action( "add_meta_boxes_$post_type", [ $this, 'remove_meta_boxes' ] );
		add_filter( 'rwmb_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_filter( 'rwmb_post_name_field_meta', [ $this, 'get_post_name' ] );
		add_filter( 'rwmb_post_name_value', '__return_empty_string' );
	}

	public function render() {
		if ( $this->is_screen() ) {
			wp_nonce_field( 'mbb-save', 'mbb_nonce' );
			echo '<div id="root" class="og"></div>';
		}
	}

	public function enqueue_wrapper() {
		if ( ! $this->is_screen() ) {
			return;
		}

		$this->enqueue();
	}

	abstract public function enqueue();

	public function save_wrapper( $post_id, $post ) {
		$parent = wp_is_post_revision( $post_id );
		if ( $parent ) {
			$post_id = $parent;
		}

		if ( ! wp_verify_nonce( rwmb_request()->post( 'mbb_nonce' ), 'mbb-save' ) ) {
			return;
		}

		$this->save( $post_id, $post );
	}

	abstract public function save( $post_id, $post );

	private function is_screen(): bool {
		return $this->post_type === get_current_screen()->id;
	}

	public function remove_meta_boxes(): void {
		remove_meta_box( 'slugdiv', '', 'normal' );
	}

	public function add_meta_boxes( $meta_boxes ) {
		$meta_boxes[] = [
			'title'      => $this->slug_meta_box_title,
			'post_types' => [ $this->post_type ],
			'context'    => 'side',
			'priority'   => 'low',
			'fields'     => [
				[
					'type' => 'text',
					'id'   => 'post_name',
				],
			],
		];
		return $meta_boxes;
	}

	public function get_post_name() {
		return get_post_field( 'post_name' );
	}
}
