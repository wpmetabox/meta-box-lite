<?php
namespace MBB;

abstract class BaseEditPage {
	protected $post_type;

	public function __construct( string $post_type ) {
		$this->post_type = $post_type;

		// Use `admin_head` to make the CSS apply immediately.
		add_action( 'admin_head', [ $this, 'hide_wp_elements' ] );

		// Remove all other notices from other plugins.
		add_action( 'admin_notices', [ $this, 'remove_notices' ], 1 );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_wrapper' ] );
	}

	public function hide_wp_elements(): void {
		if ( get_current_screen()->id !== $this->post_type ) {
			return;
		}
		?>
		<style>
			#post-body { display: none; }
		</style>
		<?php
	}

	public function remove_notices(): void {
		if ( ! $this->is_screen() ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
		$this->show_local_json_notice();
	}

	protected function show_local_json_notice(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin UI notice, not a form action.
		$action = sanitize_text_field( wp_unslash( $_GET['action'] ?? '' ) );
		if ( 'edit' !== $action ) {
			return;
		}

		if ( ! LocalJson::is_enabled() ) {
			return;
		}

		$json = JsonService::get_json( [
			'post_id'   => get_the_ID(),
			'post_type' => $this->post_type,
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

		if ( null === $json['local'] ) {
			$file_name = basename( $json['file'] );
			?>
			<div class="notice notice-warning">
				<p>
					<?php
					echo esc_html( sprintf(
						/* translators: %1$s: JSON file name, %2$s: object type label */
						__( 'No related local JSON file, a new file named "%1$s" will be created when you save the %2$s.', 'meta-box-builder' ),
						$file_name,
						$this->get_local_json_object_label()
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

	/**
	 * Object type label used in the Local JSON missing-file notice.
	 */
	protected function get_local_json_object_label(): string {
		$labels = [
			'meta-box'         => __( 'meta box', 'meta-box-builder' ),
			'mb-model'         => __( 'custom model', 'meta-box-builder' ),
			'mb-settings-page' => __( 'settings page', 'meta-box-builder' ),
			'mb-relationship'  => __( 'relationship', 'meta-box-builder' ),
		];

		return $labels[ $this->post_type ] ?? __( 'item', 'meta-box-builder' );
	}

	public function enqueue_wrapper(): void {
		if ( ! $this->is_screen() ) {
			return;
		}

		// Remove admin footer, which causes CSS issues.
		add_filter( 'admin_footer_text', '__return_empty_string' );
		remove_filter( 'update_footer', 'core_update_footer' );

		$this->enqueue();
	}

	abstract public function enqueue();

	protected function is_screen(): bool {
		return $this->post_type === get_current_screen()->id;
	}

	protected function get_menu_positions(): array {
		global $menu;

		$positions = [];
		foreach ( $menu as $position => $params ) {
			if ( ! empty( $params[0] ) ) {
				$positions[ $position ] = $this->strip_span( $params[0] );
			}
		}

		return $positions;
	}

	protected function get_menu_parents(): array {
		global $menu;

		$options = [];
		foreach ( $menu as $params ) {
			if ( ! empty( $params[0] ) && ! empty( $params[2] ) ) {
				$options[ $params[2] ] = $this->strip_span( $params[0] );
			}
		}

		return $options;
	}

	protected function strip_span( string $html ): string {
		return (string) preg_replace( '@<span .*>.*</span>@si', '', $html );
	}
}
