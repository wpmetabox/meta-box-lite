<?php
namespace MetaBox\ACF\Processors;

use MetaBox\Support\Arr;
use WP_Query;

class Taxonomies extends Base {
	protected function get_items() {
		// Process all post types at once.
		if ( ! empty( $_SESSION['processed'] ) ) {
			return [];
		}

		$query = new WP_Query( [
			'post_type'              => 'acf-taxonomy',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );
		return $query->posts;
	}

	protected function migrate_item() {
		$this->migrate_taxonomies();
		$this->disable_post();
	}

	protected function migrate_taxonomies() {
			$item                 = unserialize( $this->item->post_content ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			$plural               = Arr::get( $item, 'labels.name' );
			$singular             = Arr::get( $item, 'labels.singular_name' );
			$slug                 = Arr::get( $item, 'taxonomy' );
			$item['slug']         = $slug;
			$item['label']        = $plural;
			$item['hierarchical'] = Arr::get( $item, 'hierarchical' ) ? true : false;
			$meta_box_cb          = $item['hierarchical'] ? 'post_categories_meta_box' : 'post_tags_meta_box';
			$item['meta_box_cb']  = Arr::get( $item, 'meta_box_cb' ) ? $meta_box_cb : false;
			$item['types']        = Arr::get( $item, 'object_type', [] ) ?: [];
			$item['query_var']    = Arr::get( $item, 'query_var' ) !== 'none';
			$item['rewrite']      = [
				'slug'         => Arr::get( $item, 'rewrite.slug' ),
				'with_front'   => Arr::get( $item, 'rewrite.with_front' ) ? true : false,
				'hierarchical' => Arr::get( $item, 'rewrite.rewrite_hierarchical' ) ? true : false,
			];

			$array          = [
				'menu_name'                  => Arr::get( $item, 'labels.menu_name', $plural ) ?: $plural,
				'search_items'               => Arr::get( $item, 'labels.search_items', 'Search ' . $plural ) ?: 'Search ' . $plural,
				'popular_items'              => Arr::get( $item, 'labels.popular_items', 'Popular ' . $plural ) ?: 'Popular ' . $plural,
				'all_items'                  => Arr::get( $item, 'labels.all_items', 'All ' . $plural ) ?: 'All ' . $plural,
				'view_item'                  => Arr::get( $item, 'labels.view_item', 'View ' . $singular ) ?: 'View ' . $singular,
				'parent_item'                => Arr::get( $item, 'labels.parent_item', 'Parent ' . $singular ) ?: 'Parent ' . $singular,
				'parent_item_colon'          => Arr::get( $item, 'labels.parent_item_colon', 'Parent ' . $singular ) ?: 'Parent ' . $singular,
				'edit_item'                  => Arr::get( $item, 'labels.edit_item', 'Edit ' . $singular ) ?: 'Edit ' . $singular,
				'update_item'                => Arr::get( $item, 'labels.update_item', 'Update ' . $singular ) ?: 'Update ' . $singular,
				'add_new_item'               => Arr::get( $item, 'labels.add_new_item', 'Add new ' . $singular ) ?: 'Add new ' . $singular,
				'new_item_name'              => Arr::get( $item, 'labels.new_item_name', 'New ' . $singular . ' name' ) ?: 'New ' . $singular . ' name',
				'filter_by_item'             => Arr::get( $item, 'labels.filter_by_item', 'Filter by ' . $singular ) ?: 'Filter by ' . $singular,
				'separate_items_with_commas' => Arr::get( $item, 'labels.separate_items_with_commas', 'Separate ' . $plural . ' with commas' ) ?: 'Separate ' . $plural . ' with commas',
				'add_or_remove_items'        => Arr::get( $item, 'labels.add_or_remove_items', 'Add or remove ' . $plural ) ?: 'Add or remove ' . $plural,
				'choose_from_most_used'      => Arr::get( $item, 'labels.choose_from_most_used', 'Choose from the most used ' . $plural ) ?: 'Choose from the most used ' . $plural,
				'not_found'                  => Arr::get( $item, 'labels.not_found', 'Not ' . $plural . ' found' ) ?: 'Not ' . $plural . ' found',
				'no_terms'                   => Arr::get( $item, 'labels.no_terms', 'No ' . $plural ) ?: 'No ' . $plural,
				'items_list_navigation'      => Arr::get( $item, 'labels.items_list_navigation', $plural . ' list navigation' ) ?: $plural . ' list navigation',
				'items_list'                 => Arr::get( $item, 'labels.items_list', $plural . ' list' ) ?: $plural . ' list',
				'back_to_items'              => Arr::get( $item, 'labels.back_to_items', 'Back to ' . $plural ) ?: 'Back to ' . $plural,
			];
			$item['labels'] = array_merge( $item['labels'], $array );
			$content        = wp_json_encode( $item, JSON_UNESCAPED_UNICODE );
			$post_obj       = get_page_by_path( $singular, OBJECT, 'mb-taxonomy' );
			if ( $post_obj ) {
				wp_update_post( [
					'ID'           => $post_obj->ID,
					'post_content' => $content,
				] );
			} else {
				wp_insert_post( [
					'post_content' => $content,
					'post_type'    => 'mb-taxonomy',
					'post_title'   => $plural,
					'post_status'  => 'publish',
					'post_name'    => $singular,
				] );
			}
	}

	protected function disable_post() {
		$data = [
			'ID'          => $this->item->ID,
			'post_status' => 'acf-disabled',
		];

		wp_update_post( $data );
	}
}
