<?php
namespace MBB;

use MBB\Helpers\Data;
use MetaBox\Support\Arr;

class AdminColumns {
	public function __construct() {
		add_action( 'admin_print_styles-edit.php', [ $this, 'enqueue' ] );
		add_filter( 'manage_meta-box_posts_columns', [ $this, 'add_columns' ] );
		add_action( 'manage_meta-box_posts_custom_column', [ $this, 'show_column' ] );
	}

	public function enqueue() {
		if ( ! in_array( get_current_screen()->id, [ 'edit-meta-box', 'edit-mb-relationship', 'edit-mb-settings-page' ], true ) ) {
			return;
		}

		wp_enqueue_style( 'mbb-list', MBB_URL . 'assets/css/list.css', [], MBB_VER );
		wp_enqueue_script( 'mbb-list', MBB_URL . 'assets/js/list.js', [ 'jquery' ], MBB_VER, true );
		wp_localize_script( 'mbb-list', 'MBB', [
			'export' => esc_html__( 'Export', 'meta-box-builder' ),
			'import' => esc_html__( 'Import', 'meta-box-builder' ),
		] );

		if ( Data::is_extension_active( 'mb-frontend-submission' ) ) {
			wp_register_script( 'popper', MBB_URL . 'assets/js/popper.js', [], '2.11.6', true );
			wp_enqueue_script( 'tippy', MBB_URL . 'assets/js/tippy.js', [ 'popper' ], '6.3.7', true );
			wp_add_inline_script( 'tippy', 'tippy( ".mbb-tooltip", {placement: "top", arrow: true, animation: "fade"} );' );
		}
	}

	public function add_columns( $columns ) {
		$new_columns = [
			'for'      => __( 'Show For', 'meta-box-builder' ),
			'location' => __( 'Location', 'meta-box-builder' ),
		];
		if ( Data::is_extension_active( 'mb-frontend-submission' ) ) {
			$new_columns['shortcode'] = __( 'Shortcode', 'meta-box-builder' ) . Data::tooltip( __( 'Embed the field group in the front end for users to submit posts.', 'meta-box-builder' ) );
		}
		$columns = array_slice( $columns, 0, 2, true ) + $new_columns + array_slice( $columns, 2, null, true );
		return $columns;
	}

	public function show_column( $name ) {
		if ( ! in_array( $name, [ 'for', 'location', 'shortcode' ], true ) ) {
			return;
		}
		$data = get_post_meta( get_the_ID(), 'settings', true );
		$this->{"show_$name"}( $data );
	}

	private function show_for( $data ) {
		$object_type = Arr::get( $data, 'object_type', 'post' );

		switch ( $object_type ) {
			case 'user':
				esc_html_e( 'Users', 'meta-box-builder' );
				break;
			case 'comment':
				esc_html_e( 'Comments', 'meta-box-builder' );
				break;
			case 'setting':
				esc_html_e( 'Settings Pages', 'meta-box-builder' );
				break;
			case 'post':
				esc_html_e( 'Posts', 'meta-box-builder' );
				break;
			case 'term':
				esc_html_e( 'Taxonomies', 'meta-box-builder' );
				break;
			case 'block':
				esc_html_e( 'Blocks', 'meta-box-builder' );
				break;
		}
	}

	private function show_location( $data ) {
		$object_type = Arr::get( $data, 'object_type', 'post' );
		switch ( $object_type ) {
			case 'user':
				esc_html_e( 'All Users', 'meta-box-builder' );
				break;
			case 'comment':
				esc_html_e( 'All Comments', 'meta-box-builder' );
				break;
			case 'setting':
				$settings_pages = Data::get_setting_pages();
				$settings_pages = wp_list_pluck( $settings_pages, 'title', 'id' );
				$ids            = Arr::get( $data, 'settings_pages', [] );
				$saved          = array_intersect_key( $settings_pages, array_flip( $ids ) );
				echo wp_kses_post( implode( '<br>', $saved ) );
				break;
			case 'post':
				echo wp_kses_post( implode( '<br>', array_filter( array_map( function ( $post_type ) {
					$post_type_object = get_post_type_object( $post_type );
					return $post_type_object ? $post_type_object->labels->singular_name : '';
				}, Arr::get( $data, 'post_types', [ 'post' ] ) ) ) ) );
				break;
			case 'term':
				echo wp_kses_post( implode( '<br>', array_filter( array_map( function ( $taxonomy ) {
					$taxonomy_object = get_taxonomy( $taxonomy );
					return $taxonomy_object ? $taxonomy_object->labels->singular_name : '';
				}, Arr::get( $data, 'taxonomies', [] ) ) ) ) );
				break;
			case 'block':
				if ( isset( $data['block_json'] ) && isset( $data['block_json']['path'] ) ) {
					echo esc_html( $data['block_json']['path'] );
				}
				break;
		}
	}

	private function show_shortcode() {
		global $post;
		$shortcode = "[mb_frontend_form id='{$post->post_name}' post_fields='title,content']";
		echo '<input type="text" readonly value="' . esc_attr( $shortcode ) . '" onclick="this.select()">';
	}
}
