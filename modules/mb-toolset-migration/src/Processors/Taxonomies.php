<?php
namespace MetaBox\TS\Processors;

use MetaBox\Support\Arr;

class Taxonomies extends Base {

	protected function get_items() {

		$items = get_option( 'wpcf-custom-taxonomies' ) ?: [];
		return $items;
	}

	protected function migrate_item() {
		$items = $this->get_items();
		$i     = 0;
		foreach ( $items as $item ) {
			++$i;
			if ( $i < 3 ) {
				continue;
			}
			$plural               = Arr::get( $item, 'labels.name' );
			$singular             = Arr::get( $item, 'labels.singular_name' );
			$slug                 = Arr::get( $item, 'slug' );
			$supports             = Arr::get( $item, 'supports', [] );
			$item['query_var']    = Arr::get( $item, 'query_var_enabled' );
			$item['hierarchical'] = Arr::get( $item, 'hierarchical' ) ? true : false;
			$meta_box_cb          = $item['hierarchical'] ? 'post_categories_meta_box' : 'post_tags_meta_box';
			$item['meta_box_cb']  = Arr::get( $item, 'meta_box_cb.disabled' ) ? false : $meta_box_cb;
			$item['show_in_rest'] = Arr::get( $item, 'show_in_rest_force_disable' ) ? false : true;
			$item['types']        = [];
			foreach ( $supports as $key => $value ) {
				$item['types'][] = $key;
			}

			$array          = [
				'menu_name'                  => sprintf( Arr::get( $item, 'labels.menu_name' ), $plural ),
				'search_items'               => sprintf( Arr::get( $item, 'labels.search_items' ), $plural ),
				'popular_items'              => sprintf( Arr::get( $item, 'labels.popular_items' ), $plural ),
				'all_items'                  => sprintf( Arr::get( $item, 'labels.all_items' ), $plural ),
				'parent_item'                => sprintf( Arr::get( $item, 'labels.parent_item' ), $singular ),
				'parent_item_colon'          => sprintf( Arr::get( $item, 'labels.parent_item_colon' ), $singular ),
				'edit_item'                  => sprintf( Arr::get( $item, 'labels.edit_item' ), $singular ),
				'update_item'                => sprintf( Arr::get( $item, 'labels.update_item' ), $singular ),
				'add_new_item'               => sprintf( Arr::get( $item, 'labels.add_new_item' ), $singular ),
				'new_item_name'              => sprintf( Arr::get( $item, 'labels.new_item_name' ), $singular ),
				'separate_items_with_commas' => sprintf( Arr::get( $item, 'labels.separate_items_with_commas' ), $plural ),
				'add_or_remove_items'        => sprintf( Arr::get( $item, 'labels.add_or_remove_items' ), $plural ),
				'choose_from_most_used'      => sprintf( Arr::get( $item, 'labels.choose_from_most_used' ), $plural ),
				'view_item'                  => Arr::get( $item, 'labels.view_item', 'View ' . $singular ) ?: 'View ' . $singular,
				'filter_by_item'             => Arr::get( $item, 'labels.filter_by_item', 'Filter by ' . $singular ) ?: 'Filter by ' . $singular,
				'not_found'                  => Arr::get( $item, 'labels.not_found', 'Not ' . $plural . ' found' ) ?: 'Not ' . $plural . ' found',
				'no_terms'                   => Arr::get( $item, 'labels.no_terms', 'No ' . $plural ) ?: 'No ' . $plural,
				'items_list_navigation'      => Arr::get( $item, 'labels.items_list_navigation', $plural . ' list navigation' ) ?: $plural . ' list navigation',
				'items_list'                 => Arr::get( $item, 'labels.items_list', $plural . ' list' ) ?: $plural . ' list',
				'back_to_items'              => Arr::get( $item, 'labels.back_to_items', 'Back to ' . $plural ) ?: 'Back to ' . $plural,
			];
			$item['labels'] = array_merge( $item['labels'], $array );
			$content        = wp_json_encode( $item, JSON_UNESCAPED_UNICODE );
			$content        = str_replace( '"1"', 'true', $content );
			$post_id        = $this->get_id_by_slug( $slug, 'mb-taxonomy' );
			if ( $post_id ) {
				wp_update_post( [
					'ID'           => $post_id,
					'post_content' => $content,
				] );
			} else {
				$post_id = wp_insert_post( [
					'post_content' => $content,
					'post_type'    => 'mb-taxonomy',
					'post_title'   => $plural,
					'post_status'  => 'publish',
					'post_name'    => $slug,
				] );
			}
		}
		$items     = get_option( 'wpcf-custom-taxonomies' );
		$new_items = [];
		$i         = 0;
		foreach ( $items as $key => $value ) {
			++$i;
			if ( $i > 2 ) {
				$value['disabled'] = '1';
			}
			$new_items[ $key ] = $value;
		}
		update_option( 'wpcf-custom-taxonomies', $new_items );
		wp_send_json_success( [
			'message' => __( 'Done', 'mb-toolset-migration' ),
			'type'    => 'done',
		] );
	}
}
