<?php
namespace MBB;

use WP_Query;

class Export {
	public function __construct() {
		add_filter( 'post_row_actions', [ $this, 'add_export_link' ], 10, 2 );
		add_action( 'admin_init', [ $this, 'export' ] );
	}

	public function add_export_link( $actions, $post ) {
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

		$query = new WP_Query( [
			'post_type'              => $post_type,
			'post__in'               => $post_ids,
			'posts_per_page'         => count( $post_ids ),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		] );

		$data = [];
		foreach ( $query->posts as $post ) {
			$post_data = [
				'post_type'    => $post->post_type,
				'post_name'    => $post->post_name,
				'post_title'   => $post->post_title,
				'post_date'    => $post->post_date,
				'post_status'  => $post->post_status,
				'post_content' => $post->post_content,
			];

			$meta_keys = $this->get_meta_keys( $post->post_type );
			foreach ( $meta_keys as $meta_key ) {
				$post_data[ $meta_key ] = get_post_meta( $post->ID, $meta_key, true );
			}

			$data[] = $post_data;
		}

		$file_name = str_replace( 'mb-', '', $post_type ) . '-export';
		if ( count( $post_ids ) === 1 ) {
			$data      = reset( $data );
			$post      = $query->posts[0];
			$file_name = $post->post_name ?: sanitize_key( $post->post_title );
		}

		$output = wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

		header( 'Content-Type: application/octet-stream' );
		header( "Content-Disposition: attachment; filename=$file_name.json" );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . strlen( $output ) );

		echo wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
		die;
	}

	private function get_meta_keys( $post_type ) {
		switch ( $post_type ) {
			case 'meta-box':
				return [ 'settings', 'fields', 'data', 'meta_box' ];
			case 'mb-relationship':
				return [ 'settings', 'relationship' ];
			case 'mb-settings-page':
				return [ 'settings', 'settings_page' ];
			default:
				return [];
		}
	}
}
