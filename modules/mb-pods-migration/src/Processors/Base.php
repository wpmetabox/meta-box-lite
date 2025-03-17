<?php
namespace MetaBox\Pods\Processors;

use MetaBox\Support\Arr;

abstract class Base {
	protected $threshold = 10;
	public $item;
	protected $object_type;

	public function migrate() {
		$items = $this->get_items();
		if ( empty( $items ) ) {
			wp_send_json_success( [
				'message' => __( 'Done', 'mb-pods-migration' ),
				'type'    => 'done',
			] );
		}

		foreach ( $items as $item ) {
			$this->item = $item;
			$this->migrate_item();
		}

		if ( isset( $_SESSION['processed'] ) ) {
			$_SESSION['processed'] += count( $items );
		}
		wp_send_json_success( [
			// Translators: %d - count items.
			'message' => sprintf( __( 'Processed %d items...', 'mb-pods-migration' ), isset( $_SESSION['processed'] ) ? (int) $_SESSION['processed'] : 0 ) . '<br>',
			'type'    => 'continue',
		] );
	}

	abstract protected function get_items();
	abstract protected function migrate_item();

	protected function delete_post( $post_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE ID = %d", $post_id ) );
	}

	public function get_id_by_slug( $slug, $post_type ) {
		global $wpdb;
		if ( ! $slug ) {
			return null;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_name LIKE %s", $post_type, $slug ) );

		return $id;
	}

	public function get_col_values( $post_id, $search ) {
		global $wpdb;
		$s = '%' . $wpdb->esc_like( $search ) . '%';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cols   = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key  FROM $wpdb->postmeta WHERE post_id=%d AND meta_key LIKE %s", $post_id, $s ) );
		$checks = [];
		foreach ( $cols as $col ) {
			if ( get_post_meta( $post_id, $col, true ) ) {
				$checks[] = $col;
			}
		}

		$values = [];
		foreach ( $checks as $check ) {
			$values[] = str_replace( $search, '', $check );
		}
		return $values;
	}
}
