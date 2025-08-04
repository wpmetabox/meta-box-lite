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
	}

	public function enqueue_wrapper(): void {
		if ( ! $this->is_screen() ) {
			return;
		}

		// Remove admin footer, which causes CSS issues.
		add_filter('admin_footer_text', '__return_empty_string' );
		remove_filter( 'update_footer', 'core_update_footer' );

		$this->enqueue();
	}

	abstract public function enqueue();

	protected function is_screen(): bool {
		return $this->post_type === get_current_screen()->id;
	}
}
