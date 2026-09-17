<?php
namespace MBB;

use MBB\RestApi\Save;
use MBB\Upgrade\Ver404;
use MBBParser\Unparsers\MetaBox;
use MetaBox\Support\Arr;

class Import {
	private $upgrader_v4;

	public function __construct() {
		$this->upgrader_v4 = new Ver404();

		add_action( 'admin_footer-edit.php', [ $this, 'output_js_templates' ] );
		add_action( 'admin_init', [ $this, 'import' ] );
	}

	public function output_js_templates(): void {
		if ( ! in_array( get_current_screen()->id, [ 'edit-meta-box', 'edit-mb-relationship', 'edit-mb-settings-page', 'edit-mb-model' ], true ) ) {
			return;
		}
		?>
		<?php if ( isset( $_GET['imported'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Field groups have been imported successfully!', 'meta-box-builder' ); ?></p>
			</div>
		<?php endif; ?>

		<template id="mbb-import-form">
			<div class="mbb-import-form">
				<p><?php esc_html_e( 'Choose an exported ".json" file from your computer:', 'meta-box-builder' ); ?></p>
				<form enctype="multipart/form-data" method="post" action="">
					<?php wp_nonce_field( 'import' ); ?>
					<input type="file" name="mbb_file">
					<input type="hidden" name="mbb_post_type" value="<?php echo esc_attr( get_current_screen()->post_type ) ?>">
					<?php submit_button( esc_attr__( 'Import', 'meta-box-builder' ), 'secondary', 'submit', false, [ 'disabled' => true ] ); ?>
				</form>
			</div>
		</template>
		<?php
	}

	public function import(): void {
		// No file uploaded.
		if ( empty( $_FILES['mbb_file'] ) || empty( $_FILES['mbb_file']['tmp_name'] ) || empty( $_POST['mbb_post_type'] ) ) {
			return;
		}

		check_ajax_referer( 'import' );

		$url    = admin_url( 'edit.php?post_type=' . sanitize_text_field( wp_unslash( $_POST['mbb_post_type'] ) ) );
		$data   = file_get_contents( sanitize_text_field( $_FILES['mbb_file']['tmp_name'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$result = $this->import_json( $data );

		if ( ! $result ) {
			$result = $this->import_dat( $data );
		}

		if ( ! $result ) {
			// Translators: %s - go back URL.
			wp_die( wp_kses_post( sprintf( __( 'Invalid file data. <a href="%s">Go back</a>.', 'meta-box-builder' ), $url ) ) );
		}

		$url = add_query_arg( 'imported', 'true', $url );
		wp_safe_redirect( $url );
		die;
	}

	/**
	 * Import .json from v4.
	 */
	private function import_json( string $data ): bool {
		$posts = json_decode( $data, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return false;
		}

		// Check if $posts is multi-dimensional array or not.
		if ( array_keys( $posts ) !== range( 0, count( $posts ) - 1 ) ) {
			$posts = [ $posts ];
		}

		foreach ( $posts as $post ) {
			// Expand minimal JSON (mirrors PHP structure) into full details.
			$unparser = new MetaBox( $post );
			$unparser->unparse();

			$post = $unparser->get_settings();
			$post = Save::fix_post_date( $post );

			// Update the object owning this ID: creating a second one would take a suffixed
			// slug while keeping the imported ID, leaving two objects with the same ID.
			// Look up the slug WordPress will store, since get_page_by_path() keeps accents.
			$slug     = sanitize_title( $post['post_name'] ?? '' );
			$existing = $slug ? get_page_by_path( $slug, OBJECT, $post['post_type'] ) : null;
			if ( $existing ) {
				$post['ID'] = $existing->ID;
			}

			$post_id = wp_insert_post( $post );

			if ( ! $post_id ) {
				wp_die( wp_kses_post( sprintf(
					// Translators: %1$s - post type, %2$s - post title, %3$s - go back URL.
					__( 'Cannot import the %1$s <strong>%2$s</strong>. <a href="%3$s">Go back</a>.', 'meta-box-builder' ),
					str_replace( 'mb-', '', $post['post_type'] ),
					$post['post_title'],
					admin_url( "edit.php?post_type={$post['post_type']}" )
				) ) );
			}

			if ( is_wp_error( $post_id ) ) {
				wp_die( wp_kses_post( implode( '<br>', $post_id->get_error_messages() ) ) );
			}

			// WordPress sanitizes the slug, or derives it from the title when the JSON has no
			// ID, so the imported ID may not survive. Realign it with the slug in use.
			$new_post = get_post( $post_id );
			if ( $new_post->post_name !== $post['post_name'] ) {
				$post = $this->sync_id( $post, $new_post->post_name );
			}

			$meta_keys = Export::get_meta_keys( $post['post_type'] );
			foreach ( $meta_keys as $meta_key ) {
				update_post_meta( $post_id, $meta_key, $post[ $meta_key ] );
			}

			// After importing, we write to the json file too.
			LocalJson::use_database( [ 'post_id' => $post_id ] );
		}

		return true;
	}

	/**
	 * Store the given ID everywhere the object type keeps it.
	 *
	 * @param array  $post Imported post data.
	 * @param string $id   ID to store, which is also the post slug.
	 */
	private function sync_id( array $post, string $id ): array {
		$keys = [
			'meta-box'         => [ 'settings.id', 'meta_box.id' ],
			'mb-model'         => [ 'settings.slug', 'model.id', 'model.name' ],
			'mb-settings-page' => [ 'settings.id', 'settings_page.id' ],
			'mb-relationship'  => [ 'settings.id', 'relationship.id' ],
		];

		$post['post_name'] = $id;
		foreach ( $keys[ $post['post_type'] ] ?? [] as $key ) {
			Arr::set( $post, $key, $id );
		}

		return $post;
	}

	/**
	 * Import .dat files from < v4.
	 */
	private function import_dat( string $data ): bool {
		/**
		 * Removed excerpt_save_pre filter for meta box, which adds rel="noopener"
		 * to <a target="_blank"> links, thus braking JSON validity.
		 *
		 * @see https://elightup.freshdesk.com/a/tickets/27894
		 */
		remove_all_filters( 'excerpt_save_pre' );

		$meta_boxes = @unserialize( $data );
		if ( false === $meta_boxes ) {
			return false;
		}

		foreach ( $meta_boxes as $meta_box ) {
			$post    = unserialize( base64_decode( $meta_box ) );
			$excerpt = $post->post_excerpt;
			$excerpt = addslashes( $excerpt );

			$post_arr                 = (array) $post;
			$post_arr['post_excerpt'] = $excerpt;

			if ( isset( $post_arr['settings']['id'] ) && ! isset( $post_arr['post_name'] ) ) {
				$post_arr['post_name'] = $post_arr['settings']['id'];
			}

			if ( isset( $post_arr['relationship']['id'] ) && ! isset( $post_arr['post_name'] ) ) {
				$post_arr['post_name'] = $post_arr['relationship']['id'];
			}

			if ( isset( $post_arr['meta_box']['id'] ) && ! isset( $post_arr['post_name'] ) ) {
				$post_arr['post_name'] = $post_arr['meta_box']['id'];
			}

			unset( $post_arr['ID'] );

			$post->ID = wp_insert_post( $post_arr );

			$this->upgrader_v4->migrate_post( $post );
		}

		return true;
	}
}
