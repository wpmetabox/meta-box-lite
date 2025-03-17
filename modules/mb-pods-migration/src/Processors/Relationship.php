<?php
namespace MetaBox\Pods\Processors;

use WP_Query;

class Relationship extends Base {
	protected function get_items() {

		$query = new WP_Query( [
			'post_type'              => '_pods_field',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'order'                  => 'ASC',
			'orderby'                => 'menu_order',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}

	protected function migrate_item() {

		$items = $this->get_items();
		foreach ( $items as $item ) {
			$this->migrate_relationship( $item->ID );
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ids = $wpdb->get_col( "SELECT id FROM `{$wpdb->prefix}podsrel`" );
		foreach ( $ids as $id ) {
			$this->migrate_values( $id );
		}

		wp_send_json_success( [
			'message' => __( 'Done', 'mb-pods-migration' ),
			'type'    => 'done',
		] );
	}

	private function migrate_relationship( $id ) {
		$type  = get_post_meta( $id, 'type', true );
		$check = get_post_meta( $id, 'pick_object', true ) ?: '';
		if ( $type != 'pick' || $check == 'custom-simple' ) {
			return;
		}
		$post_id = $this->create_post( $id );
		$this->migrate_settings( $id, $post_id );
	}

	private function create_post( $id ) {
		$slug = get_post( $id )->post_name;
		$data = [
			'post_title'  => get_post( $id )->post_title,
			'post_type'   => 'mb-relationship',
			'post_status' => get_post( $id )->post_status,
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


	private function migrate_settings( $id, $post_id ) {
		$id_parent   = get_post( $id )->post_parent;
		$title       = get_post( $id )->post_title;
		$slug        = get_post( $id )->post_name;
		$object_type = get_post_meta( $id, 'pick_object', true );
		$from_type   = get_post( $id_parent )->post_name;
		$to_type     = get_post_meta( $id, 'pick_val', true );
		switch ( $object_type ) {
			case 'post_type':
				$type = 'post';
				break;
			case 'taxonomy':
				$type = 'term';
				break;
			default:
				$type = $object_type;
				break;
		}
		$settings = [
			'id'         => $slug,
			'reciprocal' => true,
			'menu_title' => $title,
			'from'       => [
				'object_type' => $type,
				'field'       => [
					'name' => $title,
				],
			],
			'to'         => [
				'object_type' => $type,
				'field'       => [
					'name' => $title,
				],
			],
		];
		if ( $type == 'post' ) {
			$settings['from']['post_type'] = $from_type;
			$settings['to']['post_type']   = $to_type;
		}

		if ( $type == 'term' ) {
			$settings['from']['taxonomy'] = $from_type;
			$settings['to']['taxonomy']   = $to_type;
		}
		update_post_meta( $post_id, 'relationship', $settings );
		update_post_meta( $post_id, 'settings', $settings );
	}


	private function migrate_values( $id ) {
		list( $item_id, $related_item_id, $slug, $weight ) = $this->get_data( $id );
		global $wpdb;
		$sql = "INSERT INTO `{$wpdb->prefix}mb_relationships` (`from`, `to`, `type`, `order_from`) VALUES (%d, %d, %s, %d)";

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$from    = $wpdb->get_results( "SELECT `from`, `to` FROM `{$wpdb->prefix}mb_relationships` WHERE `type` = '{$slug}'" );
		$weight += 1;
		$check   = [
			'from' => $item_id,
			'to'   => $related_item_id,
		];
		if ( self::check_insert_data( $from, $check ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query( $wpdb->prepare( $sql, (int) $item_id, (int) $related_item_id, $slug, (int) $weight ) );
		}
	}

	private function get_data( $id ) {
		global $wpdb;
		$item_id         = $this->get_col_single_value( 'podsrel', 'item_id', 'id', $id );
		$related_item_id = $this->get_col_single_value( 'podsrel', 'related_item_id', 'id', $id );
		$field_id        = $this->get_col_single_value( 'podsrel', 'field_id', 'id', $id );
		$type            = get_post( $field_id )->post_name;
		$weight          = $this->get_col_single_value( 'podsrel', 'weight', 'id', $id );

		return [ $item_id, $related_item_id, $type, $weight ];
	}

	private function get_col_single_value( $table, $col, $conditional_col, $conditional_value ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_var( $wpdb->prepare( "SELECT `{$col}` FROM `{$wpdb->prefix}{$table}` WHERE `{$conditional_col}`=%s", $conditional_value ) );
	}

	private function check_insert_data( $array, $from_to ) {
		foreach ( $array as $item ) {
			if ( $item->from === $from_to['from'] && $item->to === $from_to['to'] ) {
				return false;
			}
		}
		return true;
	}
}
