<?php
namespace MBB;

use WP_Post;
use RWMB_Switch_Field;

class ToggleStatusColumn {
	private $post_types = [ 'meta-box', 'mb-settings-page', 'mb-relationship' ];

	public function __construct() {
		foreach ( $this->post_types as $post_type ) {
			add_filter( 'manage_' . $post_type . '_posts_columns', [ $this, 'add_column' ] );
			add_action( 'manage_' . $post_type . '_posts_custom_column', [ $this, 'show_column' ], 10, 2 );
		}
		add_action( 'admin_print_styles-edit.php', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_mbb_toggle_status', [ $this, 'handle_toggle_status' ] );

		add_filter( 'display_post_states', [ $this, 'remove_draft_state_label' ], 10, 2 );
	}

	public function add_column( array $columns ): array {
		// Insert the status column after the checkbox column
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( $key === 'cb' ) {
				$new_columns['status'] = __( 'Status', 'meta-box-builder' );
			}
		}
		return $new_columns;
	}

	public function show_column( string $column, int $post_id ): void {
		if ( $column !== 'status' ) {
			return;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		// Use the switch markup and styles from Meta Box.
		$field = RWMB_Switch_Field::normalize( [
			'type'       => 'switch',
			'attributes' => [
				'data-id' => $post_id,
			],
		] );
		echo RWMB_Switch_Field::html( $post->post_status === 'publish', $field );
	}

	public function enqueue_scripts(): void {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, $this->post_types, true ) ) {
			return;
		}

		wp_enqueue_script(
			'mbb-status-toggle',
			MBB_URL . 'assets/js/status-toggle.js',
			[ 'jquery' ],
			filemtime( MBB_DIR . 'assets/js/status-toggle.js' ),
			true
		);

		wp_localize_script( 'mbb-status-toggle', 'mbbStatusToggle', [
			'nonce' => wp_create_nonce( 'toggle-status' ),
		] );

		// Use the switch styles from Meta Box.
		RWMB_Switch_Field::admin_enqueue_scripts();
	}

	public function handle_toggle_status(): void {
		check_ajax_referer( 'toggle-status' );

		$post_id = intval( $_POST['post_id'] ?? 0 );
		if ( ! $post_id ) {
			wp_send_json_error( [
				'message' => __( 'Invalid post ID', 'meta-box-builder' ),
			] );
		}

		$new_status = empty( $_POST['checked'] ) || $_POST['checked'] === 'false' ? 'draft' : 'publish';

		$result = wp_update_post( [
			'ID'          => $post_id,
			'post_status' => $new_status,
		] );

		if ( ! $result || is_wp_error( $result ) ) {
			wp_send_json_error( [
				'message' => __( 'Failed to update post status', 'meta-box-builder' ),
			] );
		}

		wp_send_json_success();
	}

	public function remove_draft_state_label( array $states, WP_Post $post ): array {
		if ( get_post_status( $post ) === 'draft' ) {
			unset( $states['draft'] );
		}
		return $states;
	}
}
