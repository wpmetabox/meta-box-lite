<?php
namespace MetaBox\TS\Processors;

use MetaBox\Support\Arr;

class PostTypes extends Base {

	protected function get_items() {

		$items      = get_option( 'wpcf-custom-types' ) ?: [];
		$exclude    = $this->get_exclude_post_types();
		$data_items = [];
		foreach ( $items as $key => $item ) {
			if ( ! in_array( $key, $exclude ) ) {
				$data_items[ $key ] = $item;
			}
		}

		return $data_items;
	}

	private function get_exclude_post_types() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_col( $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type=%s AND post_status=%s", 'wp-types-group', 'hidden' ) );
	}

	protected function migrate_item() {
		$items = $this->get_items();
		foreach ( $items as $item ) {
			$plural                = Arr::get( $item, 'labels.name' );
			$singular              = Arr::get( $item, 'labels.singular_name' );
			$slug                  = Arr::get( $item, 'slug' );
			$item['menu_position'] = (int) Arr::get( $item, 'menu_position' ) ?: '';
			$item['archive_slug']  = Arr::get( $item, 'has_archive_slug' );
			$item['icon_type']     = 'dashicons';
			$item['icon']          = 'dashicons-' . Arr::get( $item, 'icon' ) ?: 'dashicons-admin-post';
			$item['hierarchical']  = Arr::get( $item, 'hierarchical' ) ? true : false;
			$supports              = Arr::get( $item, 'supports', [] );
			$taxonomies            = Arr::get( $item, 'taxonomies', [] );
			$item['supports']      = [];
			$item['taxonomies']    = [];
			foreach ( $supports as $key => $value ) {
				$item['supports'][] = $key;
			}
			foreach ( $taxonomies as $key => $value ) {
				$item['taxonomies'][] = $key;
			}

			$array          = [
				'menu_name'                => Arr::get( $item, 'labels.menu_name', $plural ) ?: $plural,
				'all_items'                => Arr::get( $item, 'labels.all_items' ),
				'view_items'               => Arr::get( $item, 'labels.view_items', 'View ' . $plural ) ?: 'View ' . $plural,
				'search_items'             => sprintf( Arr::get( $item, 'labels.search_items' ), $plural ),
				'not_found'                => sprintf( Arr::get( $item, 'labels.not_found' ), $plural ),
				'not_found_in_trash'       => sprintf( Arr::get( $item, 'labels.not_found_in_trash' ), $plural ),
				'add_new_item'             => sprintf( Arr::get( $item, 'labels.add_new_item' ), $singular ),
				'edit_item'                => sprintf( Arr::get( $item, 'labels.edit_item' ), $singular ),
				'new_item'                 => sprintf( Arr::get( $item, 'labels.new_item' ), $singular ),
				'view_item'                => sprintf( Arr::get( $item, 'labels.view_item' ), $singular ),
				'add_new'                  => Arr::get( $item, 'labels.add_new' ),
				'parent_item_colon'        => Arr::get( $item, 'labels.parent_item_colon' ),
				'featured_image'           => Arr::get( $item, 'labels.featured_image', 'Featured image' ) ?: 'Featured image',
				'set_featured_image'       => Arr::get( $item, 'labels.set_featured_image', 'Set featured image' ) ?: 'Set featured image',
				'remove_featured_image'    => Arr::get( $item, 'labels.remove_featured_image', 'Remove featured image' ) ?: 'Remove featured image',
				'use_featured_image'       => Arr::get( $item, 'labels.use_featured_image', 'Use as featured image' ) ?: 'Use as featured image',
				'archives'                 => Arr::get( $item, 'labels.archives', $singular . ' archives' ) ?: $singular . ' archives',
				'insert_into_item'         => Arr::get( $item, 'labels.insert_into_item', 'Insert into ' . $singular ) ?: 'Insert into ' . $singular,
				'uploaded_to_this_item'    => Arr::get( $item, 'labels.uploaded_to_this_item', 'Uploaded to this ' . $singular ) ?: 'Uploaded to this ' . $singular,
				'filter_items_list'        => Arr::get( $item, 'labels.filter_items_list', 'Filter ' . $plural . ' list' ) ?: 'Filter ' . $plural . ' list',
				'items_list_navigation'    => Arr::get( $item, 'labels.items_list_navigation', $plural . ' list navigation' ) ?: $plural . ' list navigation',
				'items_list'               => Arr::get( $item, 'labels.items_list', $plural . ' list' ) ?: $plural . ' list',
				'attributes'               => Arr::get( $item, 'labels.attributes', $plural . ' attributes' ) ?: $plural . ' attributes',
				'item_published'           => Arr::get( $item, 'labels.item_published', $singular . ' published' ) ?: $singular . ' published',
				'item_published_privately' => Arr::get( $item, 'labels.item_published_privately', $singular . ' published privately' ) ?: $singular . ' published privately',
				'item_reverted_to_draft'   => Arr::get( $item, 'labels.item_published_privately', $singular . ' reverted to draft' ) ?: $singular . ' reverted to draft',
				'item_scheduled'           => Arr::get( $item, 'labels.item_scheduled', $singular . ' scheduled' ) ?: $singular . ' scheduled',
				'item_updated'             => Arr::get( $item, 'labels.item_updated', $singular . ' updated' ) ?: $singular . ' updated',
			];
			$item['labels'] = array_merge( $item['labels'], $array );
			$content        = wp_json_encode( $item, JSON_UNESCAPED_UNICODE );
			$content        = str_replace( '"1"', 'true', $content );
			$post_id        = $this->get_id_by_slug( $slug, 'mb-post-type' );

			if ( $post_id ) {
				wp_update_post( [
					'ID'           => $post_id,
					'post_content' => $content,
				] );
			} else {
				wp_insert_post( [
					'post_content' => $content,
					'post_type'    => 'mb-post-type',
					'post_title'   => $plural,
					'post_status'  => 'publish',
					'post_name'    => $slug,
				] );
			}
		}
		$items     = get_option( 'wpcf-custom-types' );
		$new_items = [];
		foreach ( $items as $key => $value ) {
			$value['disabled'] = '1';
			$new_items[ $key ] = $value;
		}
		update_option( 'wpcf-custom-types', $new_items );
		wp_send_json_success( [
			'message' => __( 'Done', 'mb-toolset-migration' ),
			'type'    => 'done',
		] );
	}
}
