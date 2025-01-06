<?php
namespace MetaBox\TS\Processors;

class Relationship extends Base {
	protected function get_items() {
		global $wpdb;
		$sql = "SELECT id FROM `{$wpdb->prefix}toolset_relationships` WHERE origin='wizard'";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_col( $sql );
	}

	protected function migrate_item() {
		$items = $this->get_items();
		foreach ( $items as $id ) {
			$post_id = $this->create_post( $id );
			$this->migrate_settings( $id, $post_id );
			$this->migrate_values( $id );
			$this->disable_post( $id );
		}

		$post_references = $this->get_post_references();
		foreach ( $post_references as $post_reference ) {
			$this->migrate_post_reference( $post_reference );
		}

		wp_send_json_success( [
			'message' => __( 'Done', 'mb-toolset-migration' ),
			'type'    => 'done',
		] );
	}

	private function get_post_references() {
		global $wpdb;
		$sql = "SELECT id FROM `{$wpdb->prefix}toolset_relationships` WHERE origin='post_reference_field'";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_col( $sql );
	}

	private function create_post( $id ) {
		$title  = $this->get_col_single_value( 'toolset_relationships', 'display_name_plural', 'id', $id );
		$status = $this->get_col_single_value( 'toolset_relationships', 'is_active', 'id', $id );
		$slug   = $this->get_col_single_value( 'toolset_relationships', 'slug', 'id', $id );
		$data   = [
			'post_title'  => $title,
			'post_type'   => 'mb-relationship',
			'post_status' => (int) $status === 1 ? 'publish' : 'draft',
			'post_name'   => $slug,
		];

		$post_id = $this->get_col_single_value( 'posts', 'ID', 'post_name', $slug );
		if ( $post_id ) {
			$data['ID'] = $post_id;
			wp_update_post( $data );
		} else {
			$post_id = wp_insert_post( $data );
		}
		return $post_id;
	}

	private function disable_post( $id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}toolset_relationships` SET is_active='0' WHERE id=%d", $id ) );
	}

	private function migrate_settings( $id, $post_id ) {
		$title        = $this->get_col_single_value( 'toolset_relationships', 'display_name_plural', 'id', $id );
		$slug         = $this->get_col_single_value( 'toolset_relationships', 'slug', 'id', $id );
		$from_post    = $this->get_col_single_value( 'toolset_relationships', 'parent_types', 'id', $id );
		$from_type    = $this->get_col_single_value( 'toolset_type_sets', 'type', 'set_id', $from_post );
		$to_post      = $this->get_col_single_value( 'toolset_relationships', 'child_types', 'id', $id );
		$to_type      = $this->get_col_single_value( 'toolset_type_sets', 'type', 'set_id', $to_post );
		$relationship = [
			'id'         => $slug,
			'menu_title' => $title,
			'from'       => $from_type,
			'to'         => $to_type,
		];
		$settings     = [
			'id'         => $slug,
			'menu_title' => $title,
			'from'       => [
				'object_type' => 'post',
				'post_type'   => $from_type,
				'taxonomy'    => 'category',
			],
			'to'         => [
				'object_type' => 'post',
				'post_type'   => $to_type,
				'taxonomy'    => 'category',
			],
		];
		update_post_meta( $post_id, 'relationship', $relationship );
		update_post_meta( $post_id, 'settings', $settings );
	}

	private function migrate_values( $id ) {
		list( $parent_values, $child_values, $slug ) = $this->get_data( $id );

		global $wpdb;
		$from = $this->get_col_values( 'mb_relationships', 'from', 'type', $slug );

		foreach ( $parent_values as $key => $value ) {
			if ( ! in_array( $value, $from ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query( $wpdb->prepare( "INSERT INTO `{$wpdb->prefix}mb_relationships` (`from`, `to`, `type`) VALUES (%d, %d, %s)", (int) $value, (int) $child_values[ $key ], $slug ) );
			}
		}
	}

	private function migrate_post_reference( $id ) {
		list( $parent_values, $child_values, $slug ) = $this->get_data( $id );
		foreach ( $parent_values as $key => $value ) {
			update_post_meta( $child_values[ $key ], $slug, $value );
		}
	}

	private function get_data( $ref_id ) {
		$parent_ids    = $this->get_col_values( 'toolset_associations', 'parent_id', 'relationship_id', $ref_id );
		$parent_values = [];
		foreach ( $parent_ids as $parent_id ) {
			$parent_values[] = $this->get_col_single_value( 'toolset_connected_elements', 'element_id', 'group_id', $parent_id );
		}
		$child_ids    = $this->get_col_values( 'toolset_associations', 'child_id', 'relationship_id', $ref_id );
		$child_values = [];
		foreach ( $child_ids as $child_id ) {
			$child_values[] = $this->get_col_single_value( 'toolset_connected_elements', 'element_id', 'group_id', $child_id );
		}

		$slug = $this->get_col_single_value( 'toolset_relationships', 'slug', 'id', $ref_id );

		return [ $parent_values, $child_values, $slug ];
	}

	private function get_col_single_value( $table, $col, $conditional_col, $conditional_value ) {
		global $wpdb;
		$sql = "SELECT `{$col}` FROM `{$wpdb->prefix}{$table}` WHERE `{$conditional_col}`=%s";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( $wpdb->prepare( $sql, $conditional_value ) );
	}

	private function get_col_values( $table, $col, $conditional_col, $conditional_value ) {
		global $wpdb;
		$sql = "SELECT `{$col}`  FROM `{$wpdb->prefix}{$table}` WHERE `{$conditional_col}`=%s";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_col( $wpdb->prepare( $sql, $conditional_value ) );
	}
}
