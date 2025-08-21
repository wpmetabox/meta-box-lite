<?php
namespace MBB;

use MBB\Helpers\Template;
use MetaBox\Support\Data as DataHelper;
use MBB\Helpers\Data;

class Edit extends BaseEditPage {
	public function __construct( string $post_type ) {
		parent::__construct( $post_type );

		// Add dialog to review the diff.
		add_action( 'admin_footer', [ Template::class, 'render_diff_dialog' ] );
	}

	public function remove_notices(): void {
		parent::remove_notices();

		if ( $this->is_screen() ) {
			$this->show_local_json_notice();
		}
	}

	public function show_local_json_notice(): void {
		$action = $_GET['action'] ?? '';
		if ( 'edit' !== $action ) {
			return;
		}

		// Show the notice if file is not writable.
		if ( ! LocalJson::is_enabled() ) {
			return;
		}

		$json = JsonService::get_json( [
			'post_id' => get_the_ID(),
		] );

		if ( empty( $json ) ) {
			return;
		}

		$json = reset( $json );

		$is_writable = $json['is_writable'] ?? false;

		if ( ! $is_writable ) {
			?>
			<div class="notice notice-error">
				<p>
					<?php esc_html_e( 'The JSON file is not writable. Please check the file permission.', 'meta-box-builder' ) ?>
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
					__( 'No related local JSON file, a new file named "%s" will be created when you save the meta box.', 'meta-box-builder' ), $file_name ) );
					?>
				</p>
			</div>
			<?php
			return;
		}

		$is_newer = $json['is_newer'] ?? 0;

		if ( $is_newer !== 0 ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php
					echo esc_html__( 'Your database version is different than the JSON version.
						Any changes will override the JSON file.',
					'meta-box-builder' );
					?>
					<a href="javascript:;" role="button" data-dialog="<?php esc_attr_e( $json['id'] ) ?>">
						<?php esc_html_e( 'Review', 'meta-box-builder' ) ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	public function enqueue() {
		Assets::enqueue();

		wp_enqueue_code_editor( [ 'type' => 'application/x-httpd-php' ] );
		wp_enqueue_style( 'wp-edit-post' );

		wp_enqueue_style( 'rwmb-modal', RWMB_CSS_URL . 'modal.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-modal', 'path', RWMB_CSS_DIR . 'modal.css' );
		wp_enqueue_script( 'rwmb-modal', RWMB_JS_URL . 'modal.js', [ 'jquery' ], RWMB_VER, true );

		wp_enqueue_style( 'mbb-dialog', MBB_URL . 'assets/css/dialog.css', [], filemtime( MBB_DIR . 'assets/css/dialog.css' ) );
		wp_enqueue_script( 'mbb-dialog', MBB_URL . 'assets/js/dialog.js', [ 'jquery', 'wp-api-fetch' ], filemtime( MBB_DIR . 'assets/js/dialog.js' ), true );
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

		wp_enqueue_style( 'mbb-app', MBB_URL . 'assets/css/style.css', [ 'wp-components', 'code-editor' ], filemtime( MBB_DIR . 'assets/css/style.css' ) );

		$asset = require MBB_DIR . '/assets/build/app.asset.php';

		// Add extra JS libs for copy code to clipboard & block color picker.
		$asset['dependencies'] = array_merge( $asset['dependencies'], [ 'jquery', 'clipboard', 'code-editor', 'wp-color-picker' ] );

		wp_enqueue_script( 'mbb-app', MBB_URL . 'assets/build/app.js', $asset['dependencies'], $asset['version'], true );

		// Script to toggle the admin menu.
		wp_enqueue_script(
			'mbb-admin-menu',
			MBB_URL . 'assets/js/admin-menu.js',
			[],
			filemtime( MBB_DIR . 'assets/js/admin-menu.js' ),
			true
		);

		$fields = get_post_meta( get_the_ID(), 'fields', true ) ?: [];
		$fields = array_values( $fields );

		// All other fields are false by default, but save_field need to be true by default.
		$fields = array_map( function ( $field ) {
			$field['save_field'] = $field['save_field'] ?? true;
			return $field;
		}, $fields );

		$post = get_post();

		$data = [
			'adminUrl'      => admin_url(),
			'title'         => $post->post_title,
			'slug'          => $post->post_name,

			'fields'        => $fields,
			'settings'      => get_post_meta( get_the_ID(), 'settings', true ),

			'postTypes'     => Data::get_post_types(),
			'taxonomies'    => Data::get_taxonomies(),
			'settingsPages' => Data::get_setting_pages(),
			'templates'     => Data::get_templates(),
			'icons'         => DataHelper::get_dashicons(),

			'fieldCategories' => Data::get_field_categories(),

			// Extensions check.
			'extensions'    => [
				'aio'                => defined( 'META_BOX_AIO_DIR' ),
				'adminColumns'       => Data::is_extension_active( 'mb-admin-columns' ),
				'blocks'             => Data::is_extension_active( 'mb-blocks' ),
				'columns'            => Data::is_extension_active( 'meta-box-columns' ),
				'commentMeta'        => Data::is_extension_active( 'mb-comment-meta' ),
				'conditionalLogic'   => Data::is_extension_active( 'meta-box-conditional-logic' ),
				'customTable'        => Data::is_extension_active( 'mb-custom-table' ),
				'frontendSubmission' => Data::is_extension_active( 'mb-frontend-submission' ),
				'group'              => Data::is_extension_active( 'meta-box-group' ),
				'includeExclude'     => Data::is_extension_active( 'meta-box-include-exclude' ),
				'settingsPage'       => Data::is_extension_active( 'mb-settings-page' ),
				'showHide'           => Data::is_extension_active( 'meta-box-show-hide' ),
				'tabs'               => Data::is_extension_active( 'meta-box-tabs' ),
				'termMeta'           => Data::is_extension_active( 'mb-term-meta' ),
				'userMeta'           => Data::is_extension_active( 'mb-user-meta' ),
				'revision'           => Data::is_extension_active( 'mb-revision' ),
				'views'              => Data::is_extension_active( 'mb-views' ),
			],

			'assetsBaseUrl' => MBB_URL . 'assets',

			'texts'         => [
				'saving' => __( 'Saving...', 'meta-box-builder' ),
			],
		];

		$data = apply_filters( 'mbb_app_data', $data );

		wp_localize_script( 'mbb-app', 'MbbApp', $data );
	}
}
