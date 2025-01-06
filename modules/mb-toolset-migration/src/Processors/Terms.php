<?php
namespace MetaBox\TS\Processors;

use MetaBox\Support\Data as Helper;

class Terms extends Base {
	protected $object_type = 'term';

	protected function get_items() {
		$field_group_ids = $this->get_field_group_ids();
		if ( empty( $field_group_ids ) ) {
			return [];
		}

		$terms = get_terms( [
			'taxonomy'   => array_keys( Helper::get_taxonomies() ),
			'hide_empty' => false,
			'number'     => $this->threshold,
			'offset'     => isset( $_SESSION['processed'] ) ? (int) $_SESSION['processed'] : 0,
			'fields'     => 'ids',
		] );

		return $terms;
	}

	protected function migrate_item() {
		$this->migrate_fields();
	}

	private function migrate_fields() {
		$fields = new Data\Fields( $this->get_field_group_ids(), $this );
		$fields->migrate_fields();
	}
}
