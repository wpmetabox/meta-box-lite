<?php
namespace MetaBox\Pods;

class Ajax {
	public function __construct() {
		add_action( 'wp_ajax_mbpods_reset_counter', [ $this, 'reset_counter' ] );
		add_action( 'wp_ajax_mbpods_migrate', [ $this, 'migrate' ] );
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
			'settings_pages',
			'field_groups',
			'relationship',
		], true ) ) {
			return;
		}
		$type  = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $type ) ) );
		$class = "MetaBox\Pods\Processors\\$type";
		return new $class();
	}
}
