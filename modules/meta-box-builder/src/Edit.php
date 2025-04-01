<?php
namespace MBB;

use MBB\Helpers\Template;
use MBBParser\Parsers\Base as BaseParser;
use MBBParser\Parsers\MetaBox as Parser;
use MetaBox\Support\Data as DataHelper;
use MBB\Helpers\Data;

class Edit extends BaseEditPage {
	public function __construct( $post_type, $slug_meta_box_title ) {
		parent::__construct( $post_type, $slug_meta_box_title );

		// Add notice if builder version is lower than json version.
		add_action( 'admin_notices', [ $this, 'version_notice' ] );
		add_action( 'admin_footer', [ Template::class, 'render_diff_dialog' ] );
	}

	public function version_notice() {
		// Only show the notice in the edit screen.
		if ( get_current_screen()->id !== $this->post_type ) {
			return;
		}
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

	public function add_meta_boxes( $meta_boxes ) {
		$meta_boxes = parent::add_meta_boxes( $meta_boxes );

		$meta_boxes[] = [
			'title'      => esc_html__( 'Documentation', 'meta-box-builder' ),
			'id'         => 'mbb-documentation',
			'post_types' => [ $this->post_type ],
			'context'    => 'side',
			'priority'   => 'low',
			'fields'     => [
				[
					'type' => 'custom_html',
					'std'  => '<ul>
						<li><span class="dashicons dashicons-media-document"></span> <a href="https://docs.metabox.io/extensions/meta-box-builder/" target="_blank">' . esc_html__( 'Documentation', 'meta-box-builder' ) /* phpcs:ignore WordPress.WP.I18n.TextDomainMismatch */ . '</a></li>
						<li><span class="dashicons dashicons-video-alt3"></span> <a href="https://youtu.be/_DaFUt92kYY" target="_blank">' . esc_html__( 'How to create custom fields', 'meta-box-builder' ) /* phpcs:ignore WordPress.WP.I18n.TextDomainMismatch */ . '</a></li>
						<li><span class="dashicons dashicons-video-alt3"></span> <a href="https://youtu.be/WWeaM5vIAwM" target="_blank">' . esc_html__( 'Understanding field types', 'meta-box-builder' ) /* phpcs:ignore WordPress.WP.I18n.TextDomainMismatch */ . '</a></li>
					</ul>',
				],
			],
		];

		return $meta_boxes;
	}

	public function enqueue() {
		wp_enqueue_code_editor( [ 'type' => 'application/x-httpd-php' ] );

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
		wp_enqueue_script( 'mbb-app', MBB_URL . 'assets/js/app.js', [ 'jquery', 'wp-element', 'wp-components', 'wp-i18n', 'clipboard', 'wp-color-picker', 'code-editor' ], filemtime( MBB_DIR . 'assets/js/app.js' ), true );

		$fields = get_post_meta( get_the_ID(), 'fields', true ) ?: [];
		$fields = array_values( $fields );

		// All other fields are false by default, but save_field need to be true by default.
		$fields = array_map( function ( $field ) {
			$field['save_field'] = $field['save_field'] ?? true;
			return $field;
		}, $fields );

		$data = [
			'fields'        => $fields,
			'settings'      => get_post_meta( get_the_ID(), 'settings', true ),
			'data'          => get_post_meta( get_the_ID(), 'data', true ),

			'rest'          => untrailingslashit( rest_url() ),
			'nonce'         => wp_create_nonce( 'wp_rest' ),

			'postTypes'     => Data::get_post_types(),
			'taxonomies'    => Data::get_taxonomies(),
			'settingsPages' => Data::get_setting_pages(),
			'templates'     => Data::get_templates(),
			'icons'         => DataHelper::get_dashicons(),

			// Extensions check.
			'extensions'    => [
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
		];

		$data = apply_filters( 'mbb_app_data', $data );

		wp_localize_script( 'mbb-app', 'MbbApp', $data );
	}

	public function save( $post_id, $post ) {
		// Save data for JavaScript (serialized arrays).
		$request     = rwmb_request();
		$base_parser = new BaseParser();
		$settings    = apply_filters( 'mbb_save_settings', $request->post( 'settings' ), $request );
		$fields      = apply_filters( 'mbb_save_fields', $request->post( 'fields' ), $request );
		$data        = apply_filters( 'mbb_save_data', $request->post( 'data' ), $request );

		$base_parser->set_settings( $settings )->parse_boolean_values()->parse_numeric_values();
		update_post_meta( $post_id, 'settings', $base_parser->get_settings() );

		$base_parser->set_settings( $fields )->parse_boolean_values()->parse_numeric_values();
		update_post_meta( $post_id, 'fields', $base_parser->get_settings() );

		$base_parser->set_settings( $data )->parse_boolean_values()->parse_numeric_values();
		update_post_meta( $post_id, 'data', $base_parser->get_settings() );

		// Save parsed data for PHP (serialized array).
		$submitted_data = compact( 'fields', 'settings' );
		$submitted_data = apply_filters( 'mbb_save_submitted_data', $submitted_data, $request );

		// Set post title and slug in case they're auto-generated.
		$submitted_data['post_title'] = $post->post_title;
		$submitted_data['post_name']  = $post->post_name;

		$parser = new Parser( $submitted_data );
		$parser->parse();

		update_post_meta( $post_id, 'meta_box', $parser->get_settings() );

		// Allow developers to add actions after saving the meta box.
		do_action( 'mbb_after_save', $parser, $post_id, $submitted_data );
	}
}
