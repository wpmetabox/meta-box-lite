<?php
namespace MetaBox\TS\Processors;

use MetaBox\Support\Data as Helper;
use WP_Query;

class Posts extends Base {
	protected $object_type = 'post';

	protected function get_items() {
		$field_group_ids = $this->get_field_group_ids();
		if ( empty( $field_group_ids ) ) {
			return [];
		}

		$query = new WP_Query( [
			'post_type'              => array_keys( Helper::get_post_types() ),
			'post_status'            => 'any',
			'posts_per_page'         => $this->threshold,
			'offset'                 => isset( $_SESSION['processed'] ) ? (int) $_SESSION['processed'] : 0,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}

	protected function migrate_item() {
		$this->migrate_fields();
	}

	private function migrate_fields() {
		$fields = new Data\Fields( $this->get_field_group_ids(), $this );
		$fields->migrate_fields();
	}
}
