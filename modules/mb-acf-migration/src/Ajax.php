<?php
namespace MetaBox\ACF;

class Ajax {
	public function __construct() {
		add_action( 'wp_ajax_mbacf_reset_counter', [ $this, 'reset_counter' ] );
		add_action( 'wp_ajax_mbacf_migrate', [ $this, 'migrate' ] );
	}

	public function reset_counter() {
		if ( session_status() !== PHP_SESSION_ACTIVE ) {
			session_start();
		}
		$_SESSION['processed'] = 0;

		wp_send_json_success( [
			'message' => '',
			'type'    => 'continue',
		] );
	}

	public function migrate() {
		if ( session_status() !== PHP_SESSION_ACTIVE ) {
			session_start();
		}
		$processor = $this->get_processor();
		$processor->migrate();
	}

	private function get_processor() {
		$type = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING );
		if ( ! in_array( $type, [
			'post_types',
			'taxonomies',
			'field_groups',
			'posts',
			'terms',
			'users',
			'comments',
			'settings_pages',
		], true ) ) {
			return;
		}
		$type  = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $type ) ) );
		$class = "MetaBox\ACF\Processors\\$type";
		return new $class();
	}
}
