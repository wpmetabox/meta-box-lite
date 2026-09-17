<?php
namespace MBB\Extensions\CustomModel;

use MBB\BaseEditPage;
use MBB\Assets;
use MBB\JsonService;
use MBB\LocalJson;
use MetaBox\Support\Data;

class Edit extends BaseEditPage {
	public function __construct( string $post_type ) {
		parent::__construct( $post_type );

		add_action( 'add_meta_boxes', [ $this, 'remove_submitdiv_meta_box' ] );
	}

	public function remove_notices(): void {
		parent::remove_notices();

		if ( $this->is_screen() ) {
			$this->show_local_json_notice();
		}
	}

	public function show_local_json_notice(): void {
		$action = sanitize_text_field( wp_unslash( $_GET['action'] ?? '' ) );
		if ( 'edit' !== $action ) {
			return;
		}

		if ( ! LocalJson::is_enabled() ) {
			return;
		}

		$json = JsonService::get_json( [
			'post_id'   => get_the_ID(),
			'post_type' => 'mb-model',
		] );

		if ( empty( $json ) ) {
			return;
		}

		$json = reset( $json );

		if ( ! ( $json['is_writable'] ?? false ) ) {
			?>
			<div class="notice notice-error">
				<p>
					<?php esc_html_e( 'The JSON file is not writable. Please check the file permission.', 'meta-box-builder' ); ?>
				</p>
			</div>
			<?php
			return;
		}

		if ( $json['local'] === null ) {
			$file_name = basename( $json['file'] );
			?>
			<div class="notice notice-warning">
				<p>
					<?php
					echo esc_html( sprintf(
						/* translators: %s: JSON file name */
						__( 'No related local JSON file, a new file named "%s" will be created when you save the custom model.', 'meta-box-builder' ),
						$file_name
					) );
					?>
				</p>
			</div>
			<?php
			return;
		}

		if ( ( $json['is_newer'] ?? 0 ) !== 0 ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php esc_html_e( 'Your database version is different than the JSON version. Any changes will override the JSON file.', 'meta-box-builder' ); ?>
					<a href="javascript:;" role="button" data-dialog="<?php echo esc_attr( $json['id'] ); ?>">
						<?php esc_html_e( 'Review', 'meta-box-builder' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	public function remove_submitdiv_meta_box(): void {
		remove_meta_box( 'submitdiv', $this->post_type, 'side' );
	}

	public function enqueue(): void {
		wp_enqueue_style( 'wp-edit-post' );

		wp_enqueue_style( 'mbb-app', MBB_URL . 'assets/css/style.css', [ 'wp-components', 'code-editor' ], filemtime( MBB_DIR . 'assets/css/style.css' ) );

		wp_enqueue_style(
			'mb-custom-model-app',
			MBB_URL . 'src/Extensions/CustomModel/css/custom-model.css',
			[ 'mbb-app' ],
			filemtime( MBB_DIR . 'src/Extensions/CustomModel/css/custom-model.css' )
		);
		Assets::enqueue_font_awesome();

		wp_enqueue_code_editor( [ 'type' => 'application/x-httpd-php' ] );

		wp_enqueue_style( 'mbb-dialog', MBB_URL . 'assets/css/dialog.css', [], filemtime( MBB_DIR . 'assets/css/dialog.css' ) );
		wp_enqueue_script( 'mbb-dialog', MBB_URL . 'assets/js/dialog.js', [ 'jquery', 'wp-api-fetch' ], filemtime( MBB_DIR . 'assets/js/dialog.js' ), true );
		wp_localize_script( 'mbb-dialog', 'MBBDialog', [
			'error'    => esc_html__( 'Error!', 'meta-box-builder' ),
			'synced'   => esc_html__( 'Synced', 'meta-box-builder' ),
			'syncing'  => esc_html__( 'Syncing...', 'meta-box-builder' ),
			'newer'    => esc_html__( '(newer)', 'meta-box-builder' ),
			'postType' => 'mb-model',
		] );

		$asset = require __DIR__ . '/build/custom-model.asset.php';

		$asset['dependencies'] = array_merge( $asset['dependencies'], [ 'jquery', 'clipboard', 'code-editor' ] );
		wp_enqueue_script(
			'mb-custom-model-app',
			MBB_URL . 'src/Extensions/CustomModel/build/custom-model.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		global $wpdb;

		$data = [
			'settings'       => get_post_meta( get_the_ID(), 'settings', true ) ?: [],
			'icons'          => Data::get_dashicons(),
			'action'         => get_current_screen()->action,
			'url'            => admin_url( 'edit.php?post_type=' . get_current_screen()->id ),
			'menu_positions' => $this->get_menu_positions(),
			'menu_parents'   => $this->get_menu_parents(),
			'capabilities'   => $this->get_capabilities(),
			'tablePrefix'    => $wpdb->prefix,
			'texts'          => [
				'saving' => __( 'Saving...', 'meta-box-builder' ),
			],
		];

		wp_localize_script( 'mb-custom-model-app', 'MbbApp', $data );
	}

	private function get_capabilities(): array {
		$caps  = [];
		$roles = wp_roles();
		foreach ( $roles->roles as $role ) {
			$caps = array_merge( $caps, array_keys( $role['capabilities'] ) );
		}

		$caps = array_unique( $caps );
		sort( $caps );

		return $caps;
	}
}
