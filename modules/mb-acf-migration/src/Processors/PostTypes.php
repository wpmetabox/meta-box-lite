<?php
namespace MetaBox\ACF\Processors;

use MetaBox\Support\Arr;
use WP_Query;

class PostTypes extends Base {
	protected function get_items() {
		// Process all post types at once.
		if ( ! empty( $_SESSION['processed'] ) ) {
			return [];
		}

		$query = new WP_Query( [
			'post_type'              => 'acf-post-type',
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
		$this->disable_post();
	}

	private function migrate_post_types() {
		$item                  = unserialize( $this->item->post_content ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		$plural                = Arr::get( $item, 'labels.name' );
		$singular              = Arr::get( $item, 'labels.singular_name' );
		$slug                  = Arr::get( $item, 'post_type' );
		$item['slug']          = $slug;
		$item['label']         = $plural;
		$item['menu_position'] = (int) Arr::get( $item, 'menu_position' ) ?: '';

		$item['archive_slug'] = Arr::get( $item, 'has_archive_slug' );
		$item['icon_type']    = 'dashicons';
		$item['icon']         = Arr::get( $item, 'icon' ) ?: 'dashicons-admin-post';
		$item['hierarchical'] = Arr::get( $item, 'hierarchical' );
		$item['supports']     = Arr::get( $item, 'supports', [] ) ?: [];
		$item['taxonomies']   = Arr::get( $item, 'taxonomies', [] ) ?: [];
		$item['query_var']    = Arr::get( $item, 'query_var' ) !== 'none';
		$item['rewrite']      = [
			'slug'       => Arr::get( $item, 'rewrite.slug' ),
			'with_front' => Arr::get( $item, 'rewrite.with_front' ) ? true : false,
		];
		$singular_capability  = Arr::get( $item, 'singular_capability_name' );
		$capability_type      = [ 'post', 'page' ];
		if ( in_array( $singular_capability, $capability_type, true ) ) {
			$item['capability_type'] = $singular_capability;
		} else {
			$item['capability_type'] = 'custom';
		}

		$array          = [
			'menu_name'                => Arr::get( $item, 'labels.menu_name', $plural ) ?: $plural,
			'all_items'                => Arr::get( $item, 'labels.all_items', 'All ' . $plural ) ?: 'All ' . $plural,
			'view_items'               => Arr::get( $item, 'labels.view_items', 'View ' . $plural ) ?: 'View ' . $plural,
			'search_items'             => Arr::get( $item, 'labels.search_items', 'Search ' . $plural ) ?: 'Search ' . $plural,
			'not_found'                => Arr::get( $item, 'labels.not_found', 'No ' . $plural . ' found' ) ?: 'No ' . $plural . ' found',
			'not_found_in_trash'       => Arr::get( $item, 'labels.not_found_in_trash', 'No ' . $plural . ' found in trash' ) ?: 'No ' . $plural . ' found in trash',
			'add_new_item'             => Arr::get( $item, 'labels.add_new_item', 'All new ' . $singular ) ?: 'All new ' . $singular,
			'edit_item'                => Arr::get( $item, 'labels.edit_item', 'Edit ' . $singular ) ?: 'Edit ' . $singular,
			'new_item'                 => Arr::get( $item, 'labels.new_item', 'New ' . $singular ) ?: 'New ' . $singular,
			'view_item'                => Arr::get( $item, 'labels.view_item', 'View ' . $singular ) ?: 'View ' . $singular,
			'add_new'                  => Arr::get( $item, 'labels.add_new', 'Add new' ) ?: 'Add new',
			'parent_item_colon'        => Arr::get( $item, 'labels.parent_item_colon', 'Parent ' . $singular ) ?: 'Parent ' . $singular,
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
		$post_obj       = get_page_by_path( $singular, OBJECT, 'mb-post-type' );

		if ( $post_obj ) {
			wp_update_post( [
				'ID'           => $post_obj->ID,
				'post_content' => $content,
			] );
		} else {
			wp_insert_post( [
				'post_content' => $content,
				'post_type'    => 'mb-post-type',
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
