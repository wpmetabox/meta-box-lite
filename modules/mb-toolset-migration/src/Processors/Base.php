<?php
namespace MetaBox\TS\Processors;

use MetaBox\Support\Arr;

abstract class Base {
	protected $threshold = 10;
	public $item;
	protected $object_type;
	protected $field_group_ids = null;

	public function migrate() {
		$items = $this->get_items();
		if ( empty( $items ) ) {
			wp_send_json_success( [
				'message' => __( 'Done', 'mb-toolset-migration' ),
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
			'message' => sprintf( __( 'Processed %d items...', 'mb-toolset-migration' ), isset( $_SESSION['processed'] ) ? (int) $_SESSION['processed'] : 0 ) . '<br>',
			'type'    => 'continue',
		] );
	}

	abstract protected function get_items();
	abstract protected function migrate_item();

	public function get( $key, $single = true ) {
		return get_metadata( $this->object_type, $this->item, $key, $single );
	}

	public function add( $key, $value ) {
		add_metadata( $this->object_type, $this->item, $key, $value, false );
	}

	public function update( $key, $value ) {
		update_metadata( $this->object_type, $this->item, $key, $value );
	}

	public function delete( $key ) {
		delete_metadata( $this->object_type, $this->item, $key );
	}

	protected function get_field_group_ids() {
		if ( null !== $this->field_group_ids ) {
			return $this->field_group_ids;
		}
		$this->field_group_ids = array_unique( array_map( 'absint', Arr::get( $_SESSION, "field_groups.{$this->object_type}", [] ) ) );

		return $this->field_group_ids;
	}

	public function get_id_by_slug( $slug, $post_type ) {
		global $wpdb;
		if ( ! $slug ) {
			return null;
		}
		$s = '"slug":"' . $slug . '"';
		$s = '%' . $wpdb->esc_like( $s ) . '%';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_content LIKE %s", $post_type, $s ) );

		return $id;
	}
}
