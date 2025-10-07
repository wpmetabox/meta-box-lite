<?php
namespace MBB;

use MBB\Helpers\Data;
use MBB\Helpers\Template;
use MetaBox\Support\Arr;

class AdminColumns {
	/**
	 * Current view
	 *
	 * @var string $view
	 */
	protected $view;

	protected $post_type = 'meta-box';

	public function __construct() {
		add_action( 'admin_print_styles-edit.php', [ $this, 'enqueue' ] );
		add_filter( 'manage_meta-box_posts_columns', [ $this, 'add_columns' ] );
		add_action( 'manage_meta-box_posts_custom_column', [ $this, 'show_column' ], 10, 2 );
		add_filter( 'views_edit-meta-box', [ $this, 'admin_table_views' ], 10, 1 );
		add_filter( 'bulk_actions-edit-meta-box', [ $this, 'admin_table_bulk_actions' ], 10, 1 );
		add_action( 'current_screen', [ $this, 'current_screen' ] );
		add_action( 'admin_footer', [ Template::class, 'render_diff_dialog' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );

		// Delete posts should delete the json file as well.
		add_action( 'wp_trash_post', [ $this, 'delete_json' ], 10, 1 );
		add_action( 'before_delete_post', [ $this, 'delete_json' ], 10, 1 );

		add_filter( 'wp_untrash_post_status', [ $this, 'set_post_status' ], 10, 2 );
		// Restore posts should restore the json file as well.
		add_action( 'untrashed_post', [ $this, 'restore_json' ], 10, 1 );

		add_action( 'transition_post_status', [ $this, 'handle_draft_to_publish' ], 10, 3 );
	}

	public function handle_draft_to_publish( $new_status, $old_status, $post ) {
		// Bail if LocalJson is not enabled.
		if ( ! LocalJson::is_enabled() ) {
			return;
		}

		if ( $post->post_type !== $this->post_type ) {
			return;
		}

		if ( $new_status === 'publish' && $old_status === 'draft' ) {
			// When switching from 'draft' to 'publish', the earlier meta box does not contains id
			// (since draft posts don't have post_name property).
			// So, we need to set the id for the meta box
			$meta_box = get_post_meta( $post->ID, 'meta_box', true );

			if ( ! is_array( $meta_box ) ) {
				return;
			}

			if ( ! isset( $meta_box['id'] ) ) {
				$meta_box['id'] = $post->post_name;
				update_post_meta( $post->ID, 'meta_box', $meta_box );
			}

			// Publish the json file.
			LocalJson::use_database( [
				'post_id' => $post->ID,
			] );
		}
	}

	public function set_post_status( $new_status, $post_id ) {
		// Bail if LocalJson is not enabled.
		if ( ! LocalJson::is_enabled() ) {
			return $new_status;
		}

		if ( $new_status === 'draft' ) {
			$post = get_post( $post_id );

			if ( $post->post_type !== $this->post_type ) {
				return $new_status;
			}

			return 'publish';
		}

		return $new_status;
	}

	public function delete_json( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$json = JsonService::get_json( [
			'post_id' => $post_id,
		] );

		if ( empty( $json ) ) {
			return;
		}

		$meta_box_id = array_key_first( $json );
		$file_path   = $json[ $meta_box_id ]['file'];

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		unlink( $file_path );
	}

	public function restore_json( $post_id ): bool {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		return LocalJson::use_database( [
			'post_id' => $post_id,
		] );
	}

	public function admin_notices(): void {
		if ( get_current_screen()->id !== 'edit-meta-box' ) {
			return;
		}

		// Don't show other notices.
		remove_all_actions( 'admin_notices' );

		if ( ! LocalJson::is_enabled() ) {
			return;
		}

		$custom_admin_notice = $_GET['status'] ?? '';

		$messages = [
			'imported'      => [
				'status'  => 'success',
				'message' => __( 'Imported successfully', 'meta-box-builder' ),
			],
			'import-failed' => [
				'status'  => 'error',
				'message' => __( 'Import failed', 'meta-box-builder' ),
			],
		];

		if ( ! isset( $messages[ $custom_admin_notice ] ) ) {
			return;
		}
		?>
		<div class="notice notice-<?php esc_attr_e( $messages[ $custom_admin_notice ]['status'] ) ?> is-dismissible">
			<p><?php esc_html_e( $messages[ $custom_admin_notice ]['message'] ) ?></p>
		</div>
		<?php
	}

	public function current_screen() {
		if ( ! LocalJson::is_enabled() ) {
			return;
		}
		$screen = get_current_screen();

		if ( $screen->id !== 'edit-meta-box' ) {
			return;
		}

		$this->view      = $_GET['post_status'] ?? ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- used as intval to return a page.
		$this->post_type = $_GET['post_type'] ?? 'meta-box'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- used as intval to return a page.

		$this->check_sync();

		if ( ! $this->is_status( 'sync' ) ) {
			return;
		}

		add_action( 'admin_footer', [ $this, 'render_sync_template' ], 1 );
	}

	public function check_sync() {
		if ( ! LocalJson::is_enabled() ) {
			return;
		}
		$action = $_GET['action'] ?? ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- used as intval to return a page.

		if ( $action !== 'mbb-sync' ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Not allowed', 'meta-box-builder' ) );
		}

		$id = $_GET['id'] ?? $_GET['post'] ?? ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- used as intval to return a page.

		if ( empty( $id ) ) {
			wp_die( __( 'Not found', 'meta-box-builder' ) );
		}

		// Bulk actions
		if ( ! is_array( $id ) ) {
			return;
		}

		$json = JsonService::get_json();

		$json = array_filter( $json, function ( $item, $key ) use ( $id ) {
			return in_array( $key, $id, true );
		}, ARRAY_FILTER_USE_BOTH );

		LocalJson::import_many( $json );

		wp_safe_redirect( admin_url( 'edit.php?post_type=meta-box&message=import-success' ) );
		exit;
	}

	public function render_sync_template() {
		global $wp_list_table;
		// Get table columns.
		$columns = $wp_list_table->get_columns();
		$hidden  = get_hidden_columns( $wp_list_table->screen );
		$json    = JsonService::get_json();

		// Filter where local is not null
		// and its should not imported yet.
		$json = array_filter( $json, function ( $item ) {
			return isset( $item['local'] ) && $item['is_newer'] !== 0;
		} );
		?>
		<template id="mb-sync-list">
			<tbody>
				<?php foreach ( $json as $id => $data ) : ?>
					<tr>
						<?php
						foreach ( $columns as $name => $label ) :
							$tag     = $name === 'cb' ? 'th' : 'td';
							$classes = [ $name, "column-$name" ];

							if ( $name === 'cb' ) {
								$classes[] = 'check-column';
								$label     = '';
							}

							if ( $name === 'title' ) {
								$classes[] = 'column-primary';
							}

							if ( in_array( $name, $hidden ) ) {
								$classes[] = ' hidden';
							}

							echo '<' . $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '" data-colname="' . esc_attr( $label ) . '">';

							switch ( $name ) {
								case 'cb':
									echo '<label for="cb-select-' . esc_attr( $id ) . '" class="screen-reader-text">';
									/* translators: %s: field group title */
									echo esc_html( sprintf( __( 'Select %s', 'meta-box-builder' ), $data['local']['title'] ?? '' ) );
									echo '</label>';
									echo '<input id="cb-select-' . esc_attr( $id ) . '" type="checkbox" value="' . esc_attr( $id ) . '" name="post[]">';
									break;

								case 'title':
									echo esc_html( $data['local_minimized']['title'] );
									break;

								case 'for':
									$this->show_for( $data['local_minimized'] );
									break;

								case 'path':
									$this->show_path( $id );
									break;

								case 'sync_status':
									$this->show_sync_status( $id );
									break;
							}

							echo "</$tag>";
						endforeach;
						?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</template>

		<script>
			document.addEventListener( 'DOMContentLoaded', () => {
				const template = document.querySelector( '#mb-sync-list' );
				const tbody = template.content.querySelector( 'tbody' );
				document.querySelector( '.wp-list-table tbody' ).replaceWith( tbody );
			} );
		</script>
		<?php
	}

	private function is_status( string $status ): bool {
		return isset( $_GET['post_status'] ) && $_GET['post_status'] === $status; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- used as intval to return a page.
	}

	public function admin_table_bulk_actions( $actions ) {
		if ( $this->is_status( 'sync' ) ) {
			unset( $actions['edit'] );
			unset( $actions['trash'] );

			$actions['mbb-sync'] = __( 'Sync changes', 'meta-box-builder' );
		}

		return $actions;
	}

	public function admin_table_views( $views ) {
		global $wp_list_table, $wp_query;

		$json = JsonService::get_json();

		$json = array_filter( $json, function ( $item ) {
			return isset( $item['local'] ) && $item['is_newer'] !== 0;
		} );

		$count = count( $json );

		if ( $count ) {
			$url = add_query_arg( [
				'post_status' => 'sync',
			] );

			$views['sync'] = sprintf(
				'<a %s href="%s">%s <span class="count">(%s)</span></a>',
				$this->is_status( 'sync' ) ? 'class="current"' : '',
				$url,
				esc_html( __( 'Sync available', 'meta-box-builder' ) ),
				$count
			);
		}

		if ( $this->view === 'sync' ) {
			$wp_list_table->set_pagination_args( [
				'total_items' => $count,
				'total_pages' => 1,
				'per_page'    => $count,
			] );
			$wp_query->post_count = 1; // At least one post is needed to render bulk drop-down.
		}

		return $views;
	}

	public function enqueue() {
		if ( ! in_array( get_current_screen()->id, [ 'edit-meta-box', 'edit-mb-relationship', 'edit-mb-settings-page' ], true ) ) {
			return;
		}

		wp_enqueue_style( 'mbb-list', MBB_URL . 'assets/css/list.css', [], time() );
		wp_enqueue_script( 'mbb-list', MBB_URL . 'assets/js/list.js', [ 'jquery' ], MBB_VER, true );
		wp_enqueue_script( 'mbb-dialog', MBB_URL . 'assets/js/dialog.js', [ 'jquery', 'wp-api-fetch' ], MBB_VER, true );
		wp_enqueue_style( 'mbb-dialog', MBB_URL . 'assets/css/dialog.css', [], MBB_VER );
		wp_localize_script( 'mbb-dialog', 'MBBDialog', [
			'export'         => esc_html__( 'Export', 'meta-box-builder' ),
			'import'         => esc_html__( 'Import', 'meta-box-builder' ),
			'not_imported'   => esc_html__( 'Not Imported', 'meta-box-builder' ),
			'error'          => esc_html__( 'Error!', 'meta-box-builder' ),
			'synced'         => esc_html__( 'Synced', 'meta-box-builder' ),
			'syncing'        => esc_html__( 'Syncing...', 'meta-box-builder' ),
			'newer'          => esc_html__( '(newer)', 'meta-box-builder' ),
			'sync_available' => esc_html__( 'Sync available', 'meta-box-builder' ),
		] );

		if ( Data::is_extension_active( 'mb-frontend-submission' ) ) {
			wp_register_script( 'popper', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js', [], '2.11.8', true );
			wp_enqueue_script( 'tippy', 'https://cdn.jsdelivr.net/npm/tippy.js@6.3.7/dist/tippy-bundle.umd.min.js', [ 'popper' ], '6.3.7', true );
			wp_add_inline_script( 'tippy', 'tippy( ".mbb-tooltip", {placement: "top", arrow: true, animation: "fade"} );' );
		}
	}

	public function add_columns( $columns ) {
		$new_columns = [
			'for'      => __( 'Show For', 'meta-box-builder' ),
			'location' => __( 'Location', 'meta-box-builder' ),
		];

		if ( Data::is_extension_active( 'mb-frontend-submission' ) && ! $this->is_status( 'sync' ) ) {
			$new_columns['shortcode'] = __( 'Shortcode', 'meta-box-builder' ) . Data::tooltip( __( 'Embed the field group in the front end for users to submit posts.', 'meta-box-builder' ) );
		}

		if ( LocalJson::is_enabled() && ! $this->is_status( 'trash' ) ) {
			$new_columns['path']        = __( 'Path', 'meta-box-builder' );
			$new_columns['sync_status'] = __( 'Sync status', 'meta-box-builder' ) . Data::tooltip( __( 'You must set the modified time to a Unix timestamp for it to display correctly.', 'meta-box-builder' ) );
		}

		$columns = array_slice( $columns, 0, 2, true ) + $new_columns + array_slice( $columns, 2, null, true );

		if ( $this->is_status( 'sync' ) ) {
			unset( $columns['location'] );
			unset( $columns['date'] );
		}

		return $columns;
	}

	public function show_column( $column, $post_id ) {
		if ( ! in_array( $column, [ 'for', 'location', 'shortcode', 'path', 'sync_status' ], true ) ) {
			return;
		}

		$post_name = $post_id;
		if ( in_array( $column, [ 'sync_status', 'path' ], true ) && LocalJson::is_enabled() ) {
			if ( is_numeric( $post_id ) ) {
				$post      = get_post( $post_id );
				$post_name = $post->post_name;
			}

			call_user_func( [ $this, "show_$column" ], $post_name );
			return;
		}

		$data = get_post_meta( get_the_ID(), 'settings', true );

		if ( ! is_array( $data ) ) {
			return;
		}

		$this->{"show_$column"}( $data );
	}

	private function show_sync_status( string $meta_box_id ) {
		if ( ! LocalJson::is_enabled() ) {
			return;
		}

		if ( $meta_box_id === null ) {
			return;
		}

		$json = JsonService::get_json( [
			'id' => $meta_box_id,
		] );

		if ( empty( $json ) || ! is_array( $json ) ) {
			return;
		}

		$sync_data = $json[ $meta_box_id ];

		// Empty sync data means no related json file.
		if ( empty( $sync_data ) ) {
			return;
		}

		$available_statuses = [
			'error_file_permission' => __( 'Error: Not writable', 'meta-box-builder' ),
			'sync_available'        => __( 'Sync available', 'meta-box-builder' ),
			'no_json'               => __( 'No JSON available', 'meta-box-builder' ),
			'synced'                => __( 'Synced', 'meta-box-builder' ),
		];

		$status = 'sync_available';

		if ( $sync_data['is_newer'] === 0 ) {
			$status = 'synced';
		}
		if ( ! $sync_data['is_writable'] ) {
			$status = 'error_file_permission';
		}
		if ( $sync_data['local'] === null ) {
			$status = 'no_json';
		}
		?>
		<span class="mbb-label" data-status="<?php esc_attr_e( $status ) ?>" data-for-id="<?php esc_attr_e( $meta_box_id ) ?>">
			<?php esc_html_e( $available_statuses[ $status ] ) ?>
		</span>

		<?php
		if ( $status !== 'sync_available' ) {
			return;
		}
		?>
		<div class="row-actions">
			<span class="sync">
				<a class="button-sync" data-use="json" data-id="<?php esc_html_e( $meta_box_id ) ?>" href="javascript:;">
					<?= esc_html__( 'Sync', 'meta-box-builder' ); ?>
				</a>
			</span>
			|
			<span class="review">
				<a href="javascript:;" role="button" data-dialog="<?php esc_attr_e( $meta_box_id ) ?>">
					<?= esc_html__( 'Review', 'meta-box-builder' ); ?>
				</a>
			</span>
		</div>
		<?php
	}

	private function show_for( $data ): void {
		$object_type = Arr::get( $data, 'object_type', 'post' );

		$labels = [
			'user'    => __( 'Users', 'meta-box-builder' ),
			'comment' => __( 'Comments', 'meta-box-builder' ),
			'setting' => __( 'Settings Pages', 'meta-box-builder' ),
			'post'    => __( 'Posts', 'meta-box-builder' ),
			'term'    => __( 'Taxonomies', 'meta-box-builder' ),
			'block'   => __( 'Blocks', 'meta-box-builder' ),
		];

		esc_html_e( $labels[ $object_type ] ?? '' );
	}

	/**
	 * Display human friendly file location to display in the column.
	 *
	 * @param string $file
	 * @return string
	 */
	private function format_file_location( string $file ): string {
		// Get the relative path of the file.
		$active_theme = get_template_directory();
		$plugins_path = WP_PLUGIN_DIR;

		$icon     = 'WordPress';
		$sub_path = str_replace( ABSPATH, '', $file );

		if ( str_contains( $file, $active_theme ) ) {
			$icon     = 'admin-appearance';
			$sub_path = str_replace( $active_theme, '', $file );
		}

		if ( str_contains( $file, $plugins_path ) ) {
			$icon     = 'admin-plugins';
			$sub_path = str_replace( $plugins_path, '', $file );
		}

		$icon     = esc_attr( $icon );
		$sub_path = esc_html( $sub_path );

		return "<span class=\"dashicons dashicons-{$icon}\"></span> <span class=\"mbb-sub-path\">{$sub_path}</span>";
	}

	public function show_path( string $meta_box_id ): void {
		$json = JsonService::get_json( [
			'id'        => $meta_box_id,
			'post_type' => $this->post_type,
		] );

		$data = reset( $json );

		if ( ! is_array( $data ) || ! isset( $data['file'] ) || ! file_exists( $data['file'] ) ) {
			echo esc_html__( 'File not found', 'meta-box-builder' );
			return;
		}

		echo $this->format_file_location( $data['file'] );
	}

	private function show_location( array $data ): void {
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

	private function show_shortcode(): void {
		global $post;

		$shortcode = "[mb_frontend_form id='{$post->post_name}' post_fields='title,content']";
		echo '<input type="text" readonly value="' . esc_attr( $shortcode ) . '" onclick="this.select()">';
	}
}
