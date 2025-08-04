<?php
namespace MBB;

class Export {
	public function __construct() {
		add_filter( 'post_row_actions', [ $this, 'add_export_link' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'export' ] );
	}

	/**
	 * Add export link to the post row actions.
	 *
	 * @param array<string, string> $actions
	 * @param \WP_Post              $post
	 * @return array
	 */
	public function add_export_link( $actions, $post ): array {
		if ( ! in_array( $post->post_type, [ 'meta-box', 'mb-relationship', 'mb-settings-page' ], true ) ) {
			return $actions;
		}

		$url               = wp_nonce_url( add_query_arg( [
			'action'    => 'mbb-export',
			'post_type' => $post->post_type,
			'post[]'    => $post->ID,
		] ), 'bulk-posts' ); // @see WP_List_Table::display_tablenav()
		$actions['export'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Export', 'meta-box-builder' ) . '</a>';

		return $actions;
	}

	public function export(): void {
		$action  = isset( $_REQUEST['action'] ) && 'mbb-export' === $_REQUEST['action'];
		$action2 = isset( $_REQUEST['action2'] ) && 'mbb-export' === $_REQUEST['action2'];

		if ( ( ! $action && ! $action2 ) || empty( $_REQUEST['post'] ) || empty( $_REQUEST['post_type'] ) ) {
			return;
		}

		check_ajax_referer( 'bulk-posts' );

		$post_ids  = wp_parse_id_list( wp_unslash( $_REQUEST['post'] ) );
		$post_type = sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) );

		$post_status = get_post_stati();
		$data        = JsonService::get_meta_boxes( [
			'post_type'   => $post_type,
			'post_status' => $post_status,
			'post__in'    => $post_ids,
		] );

		if ( empty( $data ) ) {
			return;
		}

		// Remove post_id & post_type from the data
		$data = array_map( function ( $item ) {
			unset( $item['post_id'] );
			unset( $item['post_type'] );

			return $item;
		}, $data );

		$data = array_values( $data );

		$file_name = str_replace( 'mb-', '', $post_type ) . '-export';
		if ( count( $post_ids ) === 1 ) {
			$data      = reset( $data );
			$file_name = $data['id'] ?? $file_name;
		}

		// Sort keys alphabetically so we have a consistent order
		ksort( $data );

		$output = wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

		header( 'Content-Type: application/octet-stream' );
		header( "Content-Disposition: attachment; filename=$file_name.json" );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . strlen( $output ) );

		echo $output;
		die;
	}

	/**
	 * Get the meta keys that saved in the database for the post type.
	 *
	 * @param string $post_type
	 * @return string[]
	 */
	public static function get_meta_keys( string $post_type ): array {
		$meta_keys = [
			'meta-box'         => [ 'settings', 'fields', 'meta_box' ],
			'mb-relationship'  => [ 'settings', 'relationship' ],
			'mb-settings-page' => [ 'settings', 'settings_page' ],
		];

		return $meta_keys[ $post_type ] ?? [];
	}
}
