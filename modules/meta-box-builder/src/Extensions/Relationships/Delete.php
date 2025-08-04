<?php
namespace MBB\Extensions\Relationships;

class Delete {
	public function __construct() {
		add_action( 'before_delete_post', [ $this, 'delete_data_in_db' ], 10, 3 );
	}

	public function delete_data_in_db( $post_id, $post ) {
		if ( $post->post_type != 'mb-relationship' ) {
			return;
		}

		$relationship = get_post_meta( $post_id, 'settings', true );
		if ( empty( $relationship['delete_data'] ) ) {
			return;
		}
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mb_relationships WHERE `type`=%s", $relationship['id'] ) );
	}
}
