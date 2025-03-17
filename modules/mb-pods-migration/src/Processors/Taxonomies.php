<?php
namespace MetaBox\Pods\Processors;

use MetaBox\Support\Arr;
use WP_Query;

class Taxonomies extends Base {

	protected function get_items() {

		if ( ! empty( $_SESSION['processed'] ) ) {
			return [];
		}

		$query = new WP_Query( [
			'post_type'              => '_pods_pod',
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
	}

	protected function migrate_taxonomies() {
			$id   = $this->item->ID;
			$type = get_post_meta( $id, 'type', true );
			$slug = $this->item->post_name;
		if ( $type != 'taxonomy' || $slug == 'category' ) {
			return;
		}
			$name     = $this->item->post_title;
			$singular = get_post_meta( $id, 'label_singular', true ) ?: $slug;
			$labels   = [
				'name'                       => $name,
				'singular_name'              => $singular,
				'menu_name'                  => get_post_meta( $id, 'label_menu_name', true ) ?: $name,
				'search_items'               => get_post_meta( $id, 'label_search_items', true ) ?: 'Search ' . $name,
				'popular_items'              => get_post_meta( $id, 'label_popular_items', true ) ?: 'Popular ' . $name,
				'all_items'                  => get_post_meta( $id, 'label_all_items', true ) ?: 'All ' . $name,
				'view_item'                  => get_post_meta( $id, 'label_view_item', true ) ?: 'View ' . $singular,
				'parent_item'                => get_post_meta( $id, 'label_parent_item', true ) ?: 'Parent ' . $singular,
				'parent_item_colon'          => get_post_meta( $id, 'label_parent_item_colon', true ) ?: 'Parent ' . $singular,
				'edit_item'                  => get_post_meta( $id, 'label_edit_item', true ) ?: 'Edit ' . $singular,
				'update_item'                => get_post_meta( $id, 'label_update_item', true ) ?: 'Update ' . $singular,
				'add_new_item'               => get_post_meta( $id, 'label_add_new_item', true ) ?: 'Add new ' . $singular,
				'new_item_name'              => get_post_meta( $id, 'label_new_item_name', true ) ?: 'New ' . $singular . ' name',
				'filter_by_item'             => get_post_meta( $id, 'label_filter_by_item', true ) ?: 'Filter by ' . $singular,
				'separate_items_with_commas' => get_post_meta( $id, 'label_separate_items_with_commas', true ) ?: 'Separate ' . $name . ' with commas',
				'add_or_remove_items'        => get_post_meta( $id, 'label_add_or_remove_items', true ) ?: 'Add or remove ' . $name,
				'choose_from_most_used'      => get_post_meta( $id, 'label_choose_from_most_used', true ) ?: 'Choose from the most used ' . $name,
				'not_found'                  => get_post_meta( $id, 'label_not_found', true ) ?: 'Not ' . $name . ' found',
				'no_terms'                   => get_post_meta( $id, 'label_no_terms', true ) ?: 'No ' . $name,
				'items_list_navigation'      => get_post_meta( $id, 'label_items_list_navigation', true ) ?: $name . ' list navigation',
				'items_list'                 => get_post_meta( $id, 'label_items_list', true ) ?: $name . ' list',
				'back_to_items'              => get_post_meta( $id, 'label_back_to_items', true ) ?: 'Back to ' . $name,
			];
			$args     = [
				'slug'               => $slug,
				'label'              => $name,
				'labels'             => $labels,
				'description'        => $this->item->post_content,
				'public'             => get_post_meta( $id, 'public', true ) ?: false,
				'hierarchical'       => get_post_meta( $id, 'hierarchical', true ) ?: false,
				'publicly_queryable' => get_post_meta( $id, 'publicly_queryable', true ) ?: false,
				'show_ui'            => get_post_meta( $id, 'show_ui', true ) ?: false,
				'show_in_nav_menus'  => get_post_meta( $id, 'show_in_nav_menus', true ) ?: false,
				'show_tagcloud'      => get_post_meta( $id, 'show_tagcloud', true ) ?: false,
				'show_in_rest'       => get_post_meta( $id, 'rest_enable', true ) ?: false,
				'query_var'          => get_post_meta( $id, 'query_var', true ) ?: false,
				'show_in_quick_edit' => get_post_meta( $id, 'show_in_quick_edit', true ) ?: false,
				'show_admin_column'  => get_post_meta( $id, 'show_admin_column', true ) ?: false,
				'rest_base'          => get_post_meta( $id, 'rest_base', true ) ?: '',
				'meta_box_cb'        => get_post_meta( $id, 'hierarchical', true ) ? 'post_categories_meta_box' : 'post_tags_meta_box',
				'show_in_menu'       => ( get_post_meta( $id, 'show_in_menu', true ) == '0' ) ? false : true,
				'types'              => $this->get_col_values( $id, 'built_in_post_types_' ) ?: [],
				'rewrite'            => [
					'with_front'   => get_post_meta( $id, 'rewrite_with_front', true ) ?: false,
					'slug'         => get_post_meta( $id, 'rewrite_custom_slug', true ) ?: '',
					'hierarchical' => get_post_meta( $id, 'rewrite_hierarchical', true ) ?: false,
				],
			];
			$content  = wp_json_encode( $args, JSON_UNESCAPED_UNICODE );
			$content  = str_replace( '"1"', 'true', $content );
			$post_id  = $this->get_id_by_slug( $slug, 'mb-taxonomy' );
			if ( $post_id ) {
				wp_update_post( [
					'ID'           => $post_id,
					'post_content' => $content,
				] );
			} else {
				wp_insert_post( [
					'post_content' => $content,
					'post_type'    => 'mb-taxonomy',
					'post_title'   => $name,
					'post_status'  => 'publish',
					'post_name'    => $slug,
				], true );
			}

			$this->delete_post( $this->item->ID );
	}
}
