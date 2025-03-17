<?php
namespace MetaBox\Pods\Processors;

use MetaBox\Support\Arr;
use WP_Query;

class PostTypes extends Base {

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
		$this->migrate_post_types();
	}

	protected function migrate_post_types() {
			$id   = $this->item->ID;
			$type = get_post_meta( $id, 'type', true );
			$slug = $this->item->post_name;
		if ( $type != 'post_type' || in_array( $slug, [ 'post', 'page' ] ) ) {
			return;
		}
			$name      = $this->item->post_title;
			$singular  = get_post_meta( $id, 'label_singular', true ) ?: $slug;
			$menu_icon = get_post_meta( $id, 'menu_icon', true ) ?: 'dashicons-admin-post';
		if ( strpos( $menu_icon, 'http' ) !== false ) {
			$icon_type = 'custom';
		}
			$show_in_menu = ( get_post_meta( $id, 'show_in_menu', true ) == '0' ) ? false : true;
			$labels       = [
				'name'                     => $name,
				'singular_name'            => $singular,
				'add_new'                  => get_post_meta( $id, 'label_add_new', true ) ?: 'Add new',
				'add_new_item'             => get_post_meta( $id, 'label_add_new_item', true ) ?: 'Add new ' . $singular,
				'edit_item'                => get_post_meta( $id, 'label_edit_item', true ) ?: 'Edit ' . $singular,
				'new_item'                 => get_post_meta( $id, 'label_new_item', true ) ?: 'New ' . $singular,
				'view_item'                => get_post_meta( $id, 'label_view_item', true ) ?: 'View ' . $singular,
				'view_items'               => get_post_meta( $id, 'label_view_items', true ) ?: 'View ' . $name,
				'search_items'             => get_post_meta( $id, 'label_search_items', true ) ?: 'Search ' . $name,
				'not_found'                => get_post_meta( $id, 'label_not_found', true ) ?: 'No ' . $name . ' found.',
				'not_found_in_trash'       => get_post_meta( $id, 'label_not_found_in_trash', true ) ?: 'No ' . $name . ' found in Trash.',
				'parent_item_colon'        => get_post_meta( $id, 'label_parent_item_colon', true ) ?: 'Parent ' . $name,
				'all_items'                => get_post_meta( $id, 'label_all_items', true ) ?: 'All ' . $name,
				'archives'                 => get_post_meta( $id, 'label_archives', true ) ?: $singular . ' Archives',
				'attributes'               => get_post_meta( $id, 'label_attributes', true ) ?: $name . ' Attributes',
				'insert_into_item'         => get_post_meta( $id, 'label_insert_into_item', true ) ?: 'Insert into ' . $singular,
				'uploaded_to_this_item'    => get_post_meta( $id, 'label_uploaded_to_this_item', true ) ?: 'Uploaded to this ' . $singular,
				'featured_image'           => get_post_meta( $id, 'label_featured_image', true ) ?: 'Featured image',
				'set_featured_image'       => get_post_meta( $id, 'label_set_featured_image', true ) ?: 'Set featured image',
				'remove_featured_image'    => get_post_meta( $id, 'label_remove_featured_image', true ) ?: 'Remove featured image',
				'use_featured_image'       => get_post_meta( $id, 'label_use_featured_image', true ) ?: 'Use as featured image',
				'menu_name'                => get_post_meta( $id, 'label_menu_name', true ) ?: $name,
				'filter_items_list'        => get_post_meta( $id, 'label_filter_items_list', true ) ?: 'Filter ' . $name . ' list',
				'filter_by_date'           => get_post_meta( $id, 'filter_by_date', true ) ?: '',
				'items_list_navigation'    => get_post_meta( $id, 'label_items_list_navigation', true ) ?: $name . ' list navigation',
				'items_list'               => get_post_meta( $id, 'label_items_list', true ) ?: $name . ' list',
				'item_published'           => get_post_meta( $id, 'label_item_published', true ) ?: $singular . ' published.',
				'item_published_privately' => get_post_meta( $id, 'label_item_published_privately', true ) ?: $singular . ' published privately.',
				'item_reverted_to_draft'   => get_post_meta( $id, 'label_item_reverted_to_draft', true ) ?: $singular . ' reverted to draft.',
				'item_scheduled'           => get_post_meta( $id, 'label_item_scheduled', true ) ?: $singular . ' scheduled.',
				'item_updated'             => get_post_meta( $id, 'label_item_updated', true ) ?: $singular . ' updated.',
			];
			$args         = [
				'slug'                => $slug,
				'label'               => $name,
				'labels'              => $labels,
				'description'         => $this->item->post_content,
				'public'              => get_post_meta( $id, 'public', true ) ?: false,
				'hierarchical'        => get_post_meta( $id, 'hierarchical', true ) ?: false,
				'exclude_from_search' => get_post_meta( $id, 'exclude_from_search', true ) ?: false,
				'publicly_queryable'  => get_post_meta( $id, 'publicly_queryable', true ) ?: false,
				'show_ui'             => get_post_meta( $id, 'show_ui', true ) ?: false,
				'show_in_nav_menus'   => get_post_meta( $id, 'show_in_nav_menus', true ) ?: false,
				'show_in_admin_bar'   => get_post_meta( $id, 'show_in_admin_bar', true ) ?: false,
				'show_in_rest'        => get_post_meta( $id, 'rest_enable', true ) ?: false,
				'query_var'           => get_post_meta( $id, 'query_var', true ) ?: false,
				'can_export'          => get_post_meta( $id, 'can_export', true ) ?: false,
				'delete_with_user'    => get_post_meta( $id, 'delete_with_user', true ) ?: false,
				'rest_base'           => get_post_meta( $id, 'rest_base', true ) ?: '',
				'show_in_menu'        => get_post_meta( $id, 'menu_location_custom', true ) ?: $show_in_menu,
				'menu_position'       => (int) get_post_meta( $id, 'menu_position', true ) ?: '',
				'icon_type'           => $icon_type ?? 'dashicons',
				'menu_icon'           => $menu_icon,
				'icon_custom'         => $menu_icon,
				'capability_type'     => get_post_meta( $id, 'capability_type', true ) ?: 'post',
				'has_archive'         => get_post_meta( $id, 'has_archive', true ) ?: false,
				'archive_slug'        => get_post_meta( $id, 'has_archive_slug', true ) ?: '',
				'supports'            => $this->get_col_values( $id, 'supports_' ) ?: [],
				'taxonomies'          => $this->get_col_values( $id, 'built_in_taxonomies_' ) ?: [],
				'rewrite'             => [
					'with_front' => get_post_meta( $id, 'rewrite_with_front', true ) ?: false,
					'slug'       => get_post_meta( $id, 'rewrite_custom_slug', true ) ?: '',
				],
			];
			$content      = wp_json_encode( $args, JSON_UNESCAPED_UNICODE );
			$content      = str_replace( '"1"', 'true', $content );
			$post_id      = $this->get_id_by_slug( $slug, 'mb-post-type' );
			if ( $post_id ) {
				wp_update_post( [
					'ID'           => $post_id,
					'post_content' => $content,
				] );
			} else {
				wp_insert_post( [
					'post_content' => $content,
					'post_type'    => 'mb-post-type',
					'post_title'   => $name,
					'post_status'  => 'publish',
					'post_name'    => $slug,
				], true );
			}

			$this->delete_post( $this->item->ID );
	}
}
